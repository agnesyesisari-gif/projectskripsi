<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PemasukanModel;
use App\Models\KegiatanModel;
use App\Models\JenisPemasukanModel;
use App\Libraries\NotifikasiLibrary;

class Pemasukan extends BaseController
{
    protected $pemasukanModel;
    protected $kegiatanModel;
    protected $jenisPemasukanModel;
    protected $notifikasiLib;
    protected $validation;

    public function __construct()
    {
        $this->pemasukanModel = new PemasukanModel();
        $this->kegiatanModel = new KegiatanModel();
        $this->jenisPemasukanModel = new JenisPemasukanModel();
        $this->notifikasiLib = new NotifikasiLibrary();
        $this->validation = \Config\Services::validation();
        
        // Set session untuk menjaga akses
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }
        
        // Cek role/otorisasi (contoh: bendahara, admin, pastor)
        $allowedRoles = ['bendahara', 'admin', 'pastor'];
        if (!in_array(session()->get('role'), $allowedRoles)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke modul pemasukan.');
        }
    }

    /**
     * Menampilkan daftar pemasukan
     */
    public function index()
    {
        $data = [
            'title' => 'Daftar Pemasukan Gereja',
            'pemasukan' => $this->pemasukanModel->getPemasukanWithDetails(),
            'total_pemasukan' => $this->pemasukanModel->getTotalPemasukan(),
            'total_pemasukan_bulan_ini' => $this->pemasukanModel->getTotalPemasukanBulanIni(),
            'bulan' => $this->request->getGet('bulan') ?? date('m'),
            'tahun' => $this->request->getGet('tahun') ?? date('Y'),
            'user_role' => session()->get('role')
        ];
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($this->request->getGet('bulan') && $this->request->getGet('tahun')) {
            $data['pemasukan'] = $this->pemasukanModel->getPemasukanByBulanTahun(
                $this->request->getGet('bulan'),
                $this->request->getGet('tahun')
            );
        }
        
        return view('admin/pemasukan/index', $data);
    }

    /**
     * Menampilkan form tambah pemasukan
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Pemasukan Baru',
            'validation' => $this->validation,
            'kegiatan_list' => $this->kegiatanModel->where('status', 'aktif')->findAll(),
            'jenis_pemasukan_list' => $this->jenisPemasukanModel->findAll(),
            'sumber_list' => ['Persembahan', 'Sumbangan', 'Iuran', 'Dana Kegiatan', 'Lainnya'],
            'user_role' => session()->get('role')
        ];
        
        return view('admin/pemasukan/create', $data);
    }

    /**
     * Menyimpan data pemasukan baru
     */
    public function store()
    {
        // Validasi input
        $rules = [
            'tanggal' => 'required|valid_date',
            'jumlah' => 'required|numeric|greater_than[0]',
            'jenis_pemasukan_id' => 'required|integer',
            'sumber' => 'required|string',
            'keterangan' => 'permit_empty|string|max_length[500]',
            'kegiatan_id' => 'permit_empty|integer',
            'bukti_pemasukan' => 'uploaded[bukti_pemasukan]|max_size[bukti_pemasukan,2048]|ext_in[bukti_pemasukan,png,jpg,jpeg,pdf]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Handle upload bukti pemasukan
        $buktiFileName = null;
        $buktiFile = $this->request->getFile('bukti_pemasukan');
        
        if ($buktiFile && $buktiFile->isValid() && !$buktiFile->hasMoved()) {
            $newName = $buktiFile->getRandomName();
            $buktiFile->move(ROOTPATH . 'public/uploads/bukti_pemasukan', $newName);
            $buktiFileName = $newName;
        }
        
        // Siapkan data untuk disimpan
        $data = [
            'tanggal' => $this->request->getPost('tanggal'),
            'jumlah' => $this->request->getPost('jumlah'),
            'jenis_pemasukan_id' => $this->request->getPost('jenis_pemasukan_id'),
            'sumber' => $this->request->getPost('sumber'),
            'keterangan' => $this->request->getPost('keterangan'),
            'kegiatan_id' => $this->request->getPost('kegiatan_id') ?: null,
            'bukti_pemasukan' => $buktiFileName,
            'dibuat_oleh' => session()->get('user_id'),
            'dibuat_pada' => date('Y-m-d H:i:s')
        ];
        
        // Simpan ke database
        if ($this->pemasukanModel->save($data)) {
            // Kirim notifikasi ke admin/pastor jika pemasukan besar
            if ($data['jumlah'] > 5000000) { // Contoh: > 5 juta
                $this->notifikasiLib->kirimNotifikasiPemasukanBesar(
                    $data['jumlah'],
                    $data['sumber'],
                    $data['tanggal']
                );
            }
            
            // Log aktivitas
            $this->logAktivitas('Menambah pemasukan baru: ' . $data['keterangan']);
            
            return redirect()->to('/admin/pemasukan')->with('success', 'Data pemasukan berhasil disimpan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data pemasukan.');
        }
    }

    /**
     * Menampilkan detail pemasukan
     */
    public function show($id)
    {
        $pemasukan = $this->pemasukanModel->getPemasukanWithDetails($id);
        
        if (!$pemasukan) {
            return redirect()->to('/admin/pemasukan')->with('error', 'Data pemasukan tidak ditemukan.');
        }
        
        $data = [
            'title' => 'Detail Pemasukan',
            'pemasukan' => $pemasukan,
            'user_role' => session()->get('role')
        ];
        
        return view('admin/pemasukan/show', $data);
    }

    /**
     * Menampilkan form edit pemasukan
     */
    public function edit($id)
    {
        $pemasukan = $this->pemasukanModel->find($id);
        
        if (!$pemasukan) {
            return redirect()->to('/admin/pemasukan')->with('error', 'Data pemasukan tidak ditemukan.');
        }
        
        // Cek otorisasi edit
        if (session()->get('role') !== 'admin' && $pemasukan['dibuat_oleh'] != session()->get('user_id')) {
            return redirect()->to('/admin/pemasukan')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini.');
        }
        
        $data = [
            'title' => 'Edit Data Pemasukan',
            'validation' => $this->validation,
            'pemasukan' => $pemasukan,
            'kegiatan_list' => $this->kegiatanModel->where('status', 'aktif')->findAll(),
            'jenis_pemasukan_list' => $this->jenisPemasukanModel->findAll(),
            'sumber_list' => ['Persembahan', 'Sumbangan', 'Iuran', 'Dana Kegiatan', 'Lainnya'],
            'user_role' => session()->get('role')
        ];
        
        return view('admin/pemasukan/edit', $data);
    }

    /**
     * Memperbarui data pemasukan
     */
    public function update($id)
    {
        // Validasi input
        $rules = [
            'tanggal' => 'required|valid_date',
            'jumlah' => 'required|numeric|greater_than[0]',
            'jenis_pemasukan_id' => 'required|integer',
            'sumber' => 'required|string',
            'keterangan' => 'permit_empty|string|max_length[500]',
            'kegiatan_id' => 'permit_empty|integer',
            'bukti_pemasukan' => 'max_size[bukti_pemasukan,2048]|ext_in[bukti_pemasukan,png,jpg,jpeg,pdf]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Cek data pemasukan
        $pemasukan = $this->pemasukanModel->find($id);
        if (!$pemasukan) {
            return redirect()->to('/admin/pemasukan')->with('error', 'Data pemasukan tidak ditemukan.');
        }
        
        // Handle upload bukti pemasukan baru (jika ada)
        $buktiFileName = $pemasukan['bukti_pemasukan'];
        $buktiFile = $this->request->getFile('bukti_pemasukan');
        
        if ($buktiFile && $buktiFile->isValid() && !$buktiFile->hasMoved()) {
            // Hapus file bukti lama jika ada
            if ($buktiFileName && file_exists(ROOTPATH . 'public/uploads/bukti_pemasukan/' . $buktiFileName)) {
                unlink(ROOTPATH . 'public/uploads/bukti_pemasukan/' . $buktiFileName);
            }
            
            $newName = $buktiFile->getRandomName();
            $buktiFile->move(ROOTPATH . 'public/uploads/bukti_pemasukan', $newName);
            $buktiFileName = $newName;
        }
        
        // Siapkan data untuk diupdate
        $data = [
            'id' => $id,
            'tanggal' => $this->request->getPost('tanggal'),
            'jumlah' => $this->request->getPost('jumlah'),
            'jenis_pemasukan_id' => $this->request->getPost('jenis_pemasukan_id'),
            'sumber' => $this->request->getPost('sumber'),
            'keterangan' => $this->request->getPost('keterangan'),
            'kegiatan_id' => $this->request->getPost('kegiatan_id') ?: null,
            'bukti_pemasukan' => $buktiFileName,
            'diperbarui_oleh' => session()->get('user_id'),
            'diperbarui_pada' => date('Y-m-d H:i:s')
        ];
        
        // Update ke database
        if ($this->pemasukanModel->save($data)) {
            // Log aktivitas
            $this->logAktivitas('Memperbarui data pemasukan ID: ' . $id);
            
            return redirect()->to('/admin/pemasukan')->with('success', 'Data pemasukan berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data pemasukan.');
        }
    }

    /**
     * Menghapus data pemasukan
     */
    public function delete($id)
    {
        // Cek otorisasi (hanya admin yang bisa menghapus)
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/pemasukan')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        
        $pemasukan = $this->pemasukanModel->find($id);
        
        if (!$pemasukan) {
            return redirect()->to('/admin/pemasukan')->with('error', 'Data pemasukan tidak ditemukan.');
        }
        
        // Hapus file bukti jika ada
        if ($pemasukan['bukti_pemasukan'] && file_exists(ROOTPATH . 'public/uploads/bukti_pemasukan/' . $pemasukan['bukti_pemasukan'])) {
            unlink(ROOTPATH . 'public/uploads/bukti_pemasukan/' . $pemasukan['bukti_pemasukan']);
        }
        
        if ($this->pemasukanModel->delete($id)) {
            // Log aktivitas
            $this->logAktivitas('Menghapus data pemasukan ID: ' . $id);
            
            return redirect()->to('/admin/pemasukan')->with('success', 'Data pemasukan berhasil dihapus.');
        } else {
            return redirect()->to('/admin/pemasukan')->with('error', 'Gagal menghapus data pemasukan.');
        }
    }

    /**
     * Menampilkan laporan pemasukan per periode
     */
    public function laporan()
    {
        $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $data = [
            'title' => 'Laporan Pemasukan',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'laporan' => $this->pemasukanModel->getLaporanPemasukan($start_date, $end_date),
            'total_per_jenis' => $this->pemasukanModel->getTotalPerJenisPemasukan($start_date, $end_date),
            'total_per_kegiatan' => $this->pemasukanModel->getTotalPerKegiatan($start_date, $end_date),
            'user_role' => session()->get('role')
        ];
        
        return view('admin/pemasukan/laporan', $data);
    }

    /**
     * Generate PDF laporan pemasukan
     */
    public function generatePDF()
    {
        $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $data = [
            'title' => 'Laporan Pemasukan Gereja',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'laporan' => $this->pemasukanModel->getLaporanPemasukan($start_date, $end_date),
            'total_per_jenis' => $this->pemasukanModel->getTotalPerJenisPemasukan($start_date, $end_date),
            'total_keseluruhan' => $this->pemasukanModel->getTotalPemasukanByTanggal($start_date, $end_date),
            'gereja_nama' => 'GEREJA KRISTEN INDONESIA' // Ganti dengan nama gereja dari setting
        ];
        
        // Menggunakan library Dompdf atau TCPDF
        return $this->generatePdfReport($data);
    }

    /**
     * Helper function untuk log aktivitas
     */
    private function logAktivitas($aktivitas)
    {
        $logModel = new \App\Models\LogAktivitasModel();
        
        $logModel->save([
            'user_id' => session()->get('user_id'),
            'aktivitas' => $aktivitas,
            'tanggal' => date('Y-m-d H:i:s'),
            'ip_address' => $this->request->getIPAddress()
        ]);
    }

    /**
     * Helper function untuk generate PDF
     */
    private function generatePdfReport($data)
    {
        // Implementasi PDF menggunakan Dompdf/TCPDF
        // Kode implementasi disesuaikan dengan library yang digunakan
        return "PDF Report";
    }
}
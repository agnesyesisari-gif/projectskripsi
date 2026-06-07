<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KeuanganModel;
use App\Models\JadwalIbadahModel;
use App\Models\ProgramKerjaModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;

class Keuangan extends BaseController
{
    use ResponseTrait;

    protected $keuanganModel;
    protected $jadwalModel;
    protected $programModel;
    protected $session;

    public function __construct()
    {
        $this->keuanganModel = new KeuanganModel();
        $this->jadwalModel = new JadwalIbadahModel();
        $this->programModel = new ProgramKerjaModel();
        $this->session = \Config\Services::session();
        
        // Middleware: Pastikan hanya admin dan bendahara yang bisa akses
        $this->checkAuthorization();
    }

    /**
     * Middleware untuk validasi role pengguna
     */
    private function checkAuthorization()
    {
        $role = $this->session->get('role');
        $allowedRoles = ['admin', 'bendahara', 'pastor'];
        
        if (!$this->session->get('isLoggedIn') || !in_array($role, $allowedRoles)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    /**
     * Dashboard Keuangan
     */
    public function index()
    {
        $data = [
            'title' => 'Dashboard Keuangan',
            'pageTitle' => 'Dashboard Keuangan Gereja',
            'user' => $this->session->get('user'),
            'role' => $this->session->get('role'),
            
            // Ringkasan Keuangan
            'total_pemasukan_bulan_ini' => $this->keuanganModel->getTotalPemasukanBulanIni(),
            'total_pengeluaran_bulan_ini' => $this->keuanganModel->getTotalPengeluaranBulanIni(),
            'saldo_akhir' => $this->keuanganModel->getSaldoAkhir(),
            
            // Transaksi Terbaru
            'transaksi_terbaru' => $this->keuanganModel->getTransaksiTerbaru(10),
            
            // Kategori Terpopuler
            'kategori_pemasukan' => $this->keuanganModel->getKategoriPemasukan(),
            'kategori_pengeluaran' => $this->keuanganModel->getKategoriPengeluaran(),
        ];

        return view('keuangan/dashboard', $data);
    }

    /**
     * Daftar Transaksi
     */
    public function transaksi()
    {
        $perPage = 20;
        $currentPage = $this->request->getVar('page') ?? 1;
        
        $jenis = $this->request->getGet('jenis'); // pemasukan/pengeluaran
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');

        $data = [
            'title' => 'Daftar Transaksi',
            'pageTitle' => 'Daftar Transaksi Keuangan',
            'user' => $this->session->get('user'),
            'role' => $this->session->get('role'),
            'transaksi' => $this->keuanganModel->getTransaksiPaginated($jenis, $bulan, $tahun, $perPage, $currentPage),
            'pager' => $this->keuanganModel->pager,
            'filters' => [
                'jenis' => $jenis,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
            'total_pemasukan' => $this->keuanganModel->getTotalByJenis('pemasukan', $bulan, $tahun),
            'total_pengeluaran' => $this->keuanganModel->getTotalByJenis('pengeluaran', $bulan, $tahun),
        ];

        return view('keuangan/transaksi', $data);
    }

    /**
     * Tambah Transaksi Pemasukan
     */
    public function tambahPemasukan()
    {
        $data = [
            'title' => 'Tambah Pemasukan',
            'pageTitle' => 'Tambah Pemasukan',
            'user' => $this->session->get('user'),
            'role' => $this->session->get('role'),
            'validation' => \Config\Services::validation(),
            'kategori' => $this->keuanganModel->getKategoriPemasukan(),
            'jadwal_ibadah' => $this->jadwalModel->findAll(), // Untuk pemasukan dari persembahan ibadah
            'program_kerja' => $this->programModel->findAll(), // Untuk pemasukan dari program kerja
        ];

        return view('keuangan/tambah_pemasukan', $data);
    }

    /**
     * Tambah Transaksi Pengeluaran
     */
    public function tambahPengeluaran()
    {
        $data = [
            'title' => 'Tambah Pengeluaran',
            'pageTitle' => 'Tambah Pengeluaran',
            'user' => $this->session->get('user'),
            'role' => $this->session->get('role'),
            'validation' => \Config\Services::validation(),
            'kategori' => $this->keuanganModel->getKategoriPengeluaran(),
            'program_kerja' => $this->programModel->findAll(), // Untuk pengeluaran program kerja
        ];

        return view('keuangan/tambah_pengeluaran', $data);
    }

    /**
     * Proses Simpan Transaksi
     */
    public function simpanTransaksi()
    {
        if (!$this->validate($this->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $jenis = $this->request->getPost('jenis');
        $nominal = str_replace('.', '', $this->request->getPost('nominal'));
        
        $data = [
            'jenis_transaksi' => $jenis,
            'kategori_id' => $this->request->getPost('kategori_id'),
            'nominal' => $nominal,
            'tanggal' => $this->request->getPost('tanggal'),
            'keterangan' => $this->request->getPost('keterangan'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'dokumen_bukti' => $this->uploadBuktiTransaksi(),
            'user_id' => $this->session->get('user_id'),
            'created_at' => Time::now(),
            
            // Relasi dengan modul lain
            'jadwal_ibadah_id' => $this->request->getPost('jadwal_ibadah_id'),
            'program_kerja_id' => $this->request->getPost('program_kerja_id'),
        ];

        try {
            $this->keuanganModel->save($data);
            
            // Log aktivitas
            $this->logAktivitas("Menambah transaksi {$jenis} sebesar Rp " . number_format($nominal, 0, ',', '.'));
            
            $message = ($jenis == 'pemasukan') ? 'Pemasukan berhasil ditambahkan' : 'Pengeluaran berhasil ditambahkan';
            return redirect()->to('/keuangan/transaksi')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Edit Transaksi
     */
    public function edit($id)
    {
        $transaksi = $this->keuanganModel->find($id);
        
        if (!$transaksi) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $data = [
            'title' => 'Edit Transaksi',
            'pageTitle' => 'Edit Transaksi',
            'user' => $this->session->get('user'),
            'role' => $this->session->get('role'),
            'validation' => \Config\Services::validation(),
            'transaksi' => $transaksi,
            'kategori_pemasukan' => $this->keuanganModel->getKategoriPemasukan(),
            'kategori_pengeluaran' => $this->keuanganModel->getKategoriPengeluaran(),
            'jadwal_ibadah' => $this->jadwalModel->findAll(),
            'program_kerja' => $this->programModel->findAll(),
        ];

        $view = ($transaksi['jenis_transaksi'] == 'pemasukan') ? 
            'keuangan/edit_pemasukan' : 'keuangan/edit_pengeluaran';
        
        return view($view, $data);
    }

    /**
     * Update Transaksi
     */
    public function update($id)
    {
        if (!$this->validate($this->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $transaksi = $this->keuanganModel->find($id);
        if (!$transaksi) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $jenis = $this->request->getPost('jenis');
        $nominal = str_replace('.', '', $this->request->getPost('nominal'));
        
        $data = [
            'id' => $id,
            'jenis_transaksi' => $jenis,
            'kategori_id' => $this->request->getPost('kategori_id'),
            'nominal' => $nominal,
            'tanggal' => $this->request->getPost('tanggal'),
            'keterangan' => $this->request->getPost('keterangan'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'updated_at' => Time::now(),
            'jadwal_ibadah_id' => $this->request->getPost('jadwal_ibadah_id'),
            'program_kerja_id' => $this->request->getPost('program_kerja_id'),
        ];

        // Upload dokumen baru jika ada
        if ($file = $this->uploadBuktiTransaksi()) {
            $data['dokumen_bukti'] = $file;
            
            // Hapus file lama jika ada
            if (!empty($transaksi['dokumen_bukti'])) {
                $this->deleteFile($transaksi['dokumen_bukti']);
            }
        }

        try {
            $this->keuanganModel->save($data);
            
            // Log aktivitas
            $this->logAktivitas("Mengupdate transaksi #{$id} menjadi Rp " . number_format($nominal, 0, ',', '.'));
            
            return redirect()->to('/keuangan/transaksi')->with('success', 'Transaksi berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Transaksi
     */
    public function delete($id)
    {
        $transaksi = $this->keuanganModel->find($id);
        
        if (!$transaksi) {
            return $this->failNotFound('Transaksi tidak ditemukan');
        }

        // Hapus file dokumen jika ada
        if (!empty($transaksi['dokumen_bukti'])) {
            $this->deleteFile($transaksi['dokumen_bukti']);
        }

        try {
            $this->keuanganModel->delete($id);
            
            // Log aktivitas
            $this->logAktivitas("Menghapus transaksi #{$id} sebesar Rp " . 
                number_format($transaksi['nominal'], 0, ',', '.'));
            
            return redirect()->to('/keuangan/transaksi')->with('success', 'Transaksi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * API untuk chart dashboard
     */
    public function apiChartData()
    {
        $year = $this->request->getGet('year') ?? date('Y');
        
        $data = [
            'pemasukan' => $this->keuanganModel->getGrafikPemasukanTahunIni($year),
            'pengeluaran' => $this->keuanganModel->getGrafikPengeluaranTahunIni($year),
        ];

        return $this->respond($data);
    }

    /**
     * Validasi Rules
     */
    private function getValidationRules()
    {
        return [
            'jenis' => 'required|in_list[pemasukan,pengeluaran]',
            'kategori_id' => 'required|integer',
            'nominal' => 'required|numeric|greater_than[0]',
            'tanggal' => 'required|valid_date',
            'keterangan' => 'permit_empty|string|max_length[500]',
            'metode_pembayaran' => 'required|in_list[tunai,transfer,bank]',
            'dokumen_bukti' => 'permit_empty|uploaded[dokumen_bukti]|max_size[dokumen_bukti,2048]|ext_in[dokumen_bukti,pdf,jpg,jpeg,png]',
        ];
    }

    /**
     * Upload Bukti Transaksi
     */
    private function uploadBuktiTransaksi()
    {
        $file = $this->request->getFile('dokumen_bukti');
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/keuangan', $newName);
            return $newName;
        }
        
        return null;
    }

    /**
     * Hapus File
     */
    private function deleteFile($filename)
    {
        $filepath = WRITEPATH . 'uploads/keuangan/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Log Aktivitas
     */
    private function logAktivitas($aktivitas)
    {
        $logModel = new \App\Models\LogAktivitasModel();
        
        $logData = [
            'user_id' => $this->session->get('user_id'),
            'aktivitas' => $aktivitas,
            'modul' => 'keuangan',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => Time::now(),
        ];
        
        $logModel->insert($logData);
    }

    /**
     * Backup Database Keuangan
     */
    public function backup()
    {
        // Hanya admin yang bisa backup
        if ($this->session->get('role') !== 'admin') {
            return redirect()->to('/keuangan')->with('error', 'Anda tidak memiliki akses!');
        }

        $backupFile = WRITEPATH . 'backups/keuangan_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Eksekusi backup (contoh sederhana)
        $command = "mysqldump -u " . env('database.default.username') . 
                  " -p" . env('database.default.password') . 
                  " " . env('database.default.database') . 
                  " keuangan > " . $backupFile;
        
        system($command);
        
        $this->logAktivitas("Melakukan backup database keuangan");
        
        return redirect()->to('/keuangan')->with('success', 'Backup berhasil dilakukan');
    }
}
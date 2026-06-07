<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PengeluaranModel;
use App\Models\ProgramKerjaModel;
use App\Models\IbadahModel;

class Pengeluaran extends BaseController
{
    protected $pengeluaranModel;
    protected $programKerjaModel;
    protected $ibadahModel;
    protected $validation;
    protected $session;

    public function __construct()
    {
        $this->pengeluaranModel = new PengeluaranModel();
        $this->programKerjaModel = new ProgramKerjaModel();
        $this->ibadahModel = new IbadahModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        
        // Middleware: Cek login
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    /**
     * Menampilkan daftar pengeluaran
     */
    public function index()
    {
        $data = [
            'title' => 'Data Pengeluaran Gereja',
            'pengeluaran' => $this->pengeluaranModel->getPengeluaranWithDetails(),
            'total_pengeluaran' => $this->pengeluaranModel->getTotalPengeluaran(),
            'total_pengeluaran_bulan_ini' => $this->pengeluaranModel->getTotalPengeluaranBulanIni(),
            'pengeluaran_per_program' => $this->pengeluaranModel->getPengeluaranPerProgram(),
            'user_level' => $this->session->get('user_level'),
            'validation' => $this->validation
        ];

        return view('admin/pengeluaran/index', $data);
    }

    /**
     * Menampilkan form tambah pengeluaran
     */
    public function create()
    {
        // Cek akses hanya untuk admin/bendahara
        if (!in_array($this->session->get('user_level'), ['admin', 'bendahara'])) {
            $this->session->setFlashdata('error', 'Anda tidak memiliki akses untuk menambah data pengeluaran.');
            return redirect()->to('/pengeluaran');
        }

        $data = [
            'title' => 'Tambah Pengeluaran Baru',
            'program_kerja' => $this->programKerjaModel->findAll(),
            'ibadah' => $this->ibadahModel->findAll(),
            'kategori' => [
                'Operasional' => 'Operasional',
                'Program' => 'Program',
                'Ibadah' => 'Ibadah',
                'Pemeliharaan' => 'Pemeliharaan',
                'Lainnya' => 'Lainnya'
            ],
            'validation' => $this->validation
        ];

        return view('admin/pengeluaran/create', $data);
    }

    /**
     * Menyimpan data pengeluaran baru
     */
    public function store()
    {
        // Cek akses hanya untuk admin/bendahara
        if (!in_array($this->session->get('user_level'), ['admin', 'bendahara'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ]);
        }

        // Validasi input
        $rules = [
            'tanggal' => 'required|valid_date',
            'kategori' => 'required',
            'keterangan' => 'required|min_length[5]|max_length[255]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'metode_pembayaran' => 'required',
            'bukti_pengeluaran' => 'uploaded[bukti_pengeluaran]|max_size[bukti_pengeluaran,2048]|mime_in[bukti_pengeluaran,image/jpg,image/jpeg,image/png,application/pdf]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Upload file bukti pengeluaran
        $file = $this->request->getFile('bukti_pengeluaran');
        $fileName = null;

        if ($file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/bukti_pengeluaran', $fileName);
        }

        // Simpan data
        $data = [
            'tanggal' => $this->request->getPost('tanggal'),
            'kategori' => $this->request->getPost('kategori'),
            'keterangan' => $this->request->getPost('keterangan'),
            'jumlah' => $this->request->getPost('jumlah'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'program_kerja_id' => $this->request->getPost('program_kerja_id') ?: null,
            'ibadah_id' => $this->request->getPost('ibadah_id') ?: null,
            'bukti_pengeluaran' => $fileName,
            'keterangan_tambahan' => $this->request->getPost('keterangan_tambahan'),
            'dibuat_oleh' => $this->session->get('user_id'),
            'status' => 'disetujui' // atau 'pending' tergantung workflow
        ];

        if ($this->pengeluaranModel->save($data)) {
            $this->session->setFlashdata('success', 'Data pengeluaran berhasil ditambahkan.');
            
            // Update total pengeluaran pada program kerja jika ada
            if ($data['program_kerja_id']) {
                $this->updateTotalPengeluaranProgram($data['program_kerja_id']);
            }
            
            return redirect()->to('/pengeluaran');
        } else {
            $this->session->setFlashdata('error', 'Gagal menambahkan data pengeluaran.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menampilkan detail pengeluaran
     */
    public function show($id)
    {
        $pengeluaran = $this->pengeluaranModel->getPengeluaranWithDetails($id);

        if (!$pengeluaran) {
            $this->session->setFlashdata('error', 'Data pengeluaran tidak ditemukan.');
            return redirect()->to('/pengeluaran');
        }

        $data = [
            'title' => 'Detail Pengeluaran',
            'pengeluaran' => $pengeluaran,
            'user_level' => $this->session->get('user_level')
        ];

        return view('admin/pengeluaran/show', $data);
    }

    /**
     * Menampilkan form edit pengeluaran
     */
    public function edit($id)
    {
        // Cek akses hanya untuk admin/bendahara
        if (!in_array($this->session->get('user_level'), ['admin', 'bendahara'])) {
            $this->session->setFlashdata('error', 'Anda tidak memiliki akses untuk mengedit data pengeluaran.');
            return redirect()->to('/pengeluaran');
        }

        $pengeluaran = $this->pengeluaranModel->find($id);

        if (!$pengeluaran) {
            $this->session->setFlashdata('error', 'Data pengeluaran tidak ditemukan.');
            return redirect()->to('/pengeluaran');
        }

        $data = [
            'title' => 'Edit Data Pengeluaran',
            'pengeluaran' => $pengeluaran,
            'program_kerja' => $this->programKerjaModel->findAll(),
            'ibadah' => $this->ibadahModel->findAll(),
            'kategori' => [
                'Operasional' => 'Operasional',
                'Program' => 'Program',
                'Ibadah' => 'Ibadah',
                'Pemeliharaan' => 'Pemeliharaan',
                'Lainnya' => 'Lainnya'
            ],
            'validation' => $this->validation
        ];

        return view('admin/pengeluaran/edit', $data);
    }

    /**
     * Mengupdate data pengeluaran
     */
    public function update($id)
    {
        // Cek akses hanya untuk admin/bendahara
        if (!in_array($this->session->get('user_level'), ['admin', 'bendahara'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ]);
        }

        // Validasi input
        $rules = [
            'tanggal' => 'required|valid_date',
            'kategori' => 'required',
            'keterangan' => 'required|min_length[5]|max_length[255]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'metode_pembayaran' => 'required',
            'bukti_pengeluaran' => 'max_size[bukti_pengeluaran,2048]|mime_in[bukti_pengeluaran,image/jpg,image/jpeg,image/png,application/pdf]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $pengeluaran = $this->pengeluaranModel->find($id);
        if (!$pengeluaran) {
            $this->session->setFlashdata('error', 'Data pengeluaran tidak ditemukan.');
            return redirect()->to('/pengeluaran');
        }

        // Upload file bukti pengeluaran jika ada perubahan
        $file = $this->request->getFile('bukti_pengeluaran');
        $fileName = $pengeluaran['bukti_pengeluaran'];

        if ($file->isValid() && !$file->hasMoved()) {
            // Hapus file lama jika ada
            if ($fileName && file_exists(WRITEPATH . 'uploads/bukti_pengeluaran/' . $fileName)) {
                unlink(WRITEPATH . 'uploads/bukti_pengeluaran/' . $fileName);
            }
            
            $fileName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/bukti_pengeluaran', $fileName);
        }

        // Update data
        $data = [
            'id' => $id,
            'tanggal' => $this->request->getPost('tanggal'),
            'kategori' => $this->request->getPost('kategori'),
            'keterangan' => $this->request->getPost('keterangan'),
            'jumlah' => $this->request->getPost('jumlah'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'program_kerja_id' => $this->request->getPost('program_kerja_id') ?: null,
            'ibadah_id' => $this->request->getPost('ibadah_id') ?: null,
            'bukti_pengeluaran' => $fileName,
            'keterangan_tambahan' => $this->request->getPost('keterangan_tambahan'),
            'diupdate_oleh' => $this->session->get('user_id')
        ];

        if ($this->pengeluaranModel->save($data)) {
            $this->session->setFlashdata('success', 'Data pengeluaran berhasil diupdate.');
            
            // Update total pengeluaran pada program kerja jika ada perubahan
            if ($pengeluaran['program_kerja_id'] != $data['program_kerja_id'] || $pengeluaran['jumlah'] != $data['jumlah']) {
                if ($pengeluaran['program_kerja_id']) {
                    $this->updateTotalPengeluaranProgram($pengeluaran['program_kerja_id']);
                }
                if ($data['program_kerja_id']) {
                    $this->updateTotalPengeluaranProgram($data['program_kerja_id']);
                }
            }
            
            return redirect()->to('/pengeluaran');
        } else {
            $this->session->setFlashdata('error', 'Gagal mengupdate data pengeluaran.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menghapus data pengeluaran
     */
    public function delete($id)
    {
        // Cek akses hanya untuk admin
        if ($this->session->get('user_level') !== 'admin') {
            $this->session->setFlashdata('error', 'Anda tidak memiliki akses untuk menghapus data pengeluaran.');
            return redirect()->to('/pengeluaran');
        }

        $pengeluaran = $this->pengeluaranModel->find($id);
        if (!$pengeluaran) {
            $this->session->setFlashdata('error', 'Data pengeluaran tidak ditemukan.');
            return redirect()->to('/pengeluaran');
        }

        // Hapus file bukti jika ada
        if ($pengeluaran['bukti_pengeluaran'] && file_exists(WRITEPATH . 'uploads/bukti_pengeluaran/' . $pengeluaran['bukti_pengeluaran'])) {
            unlink(WRITEPATH . 'uploads/bukti_pengeluaran/' . $pengeluaran['bukti_pengeluaran']);
        }

        if ($this->pengeluaranModel->delete($id)) {
            $this->session->setFlashdata('success', 'Data pengeluaran berhasil dihapus.');
            
            // Update total pengeluaran pada program kerja jika ada
            if ($pengeluaran['program_kerja_id']) {
                $this->updateTotalPengeluaranProgram($pengeluaran['program_kerja_id']);
            }
        } else {
            $this->session->setFlashdata('error', 'Gagal menghapus data pengeluaran.');
        }

        return redirect()->to('/pengeluaran');
    }

    /**
     * Menampilkan laporan pengeluaran
     */
    public function laporan()
    {
        $month = $this->request->getGet('month') ?: date('Y-m');
        $year = $this->request->getGet('year') ?: date('Y');
        $kategori = $this->request->getGet('kategori');
        
        $data = [
            'title' => 'Laporan Pengeluaran',
            'pengeluaran_bulanan' => $this->pengeluaranModel->getPengeluaranBulanan($month),
            'pengeluaran_tahunan' => $this->pengeluaranModel->getPengeluaranTahunan($year),
            'pengeluaran_per_kategori' => $this->pengeluaranModel->getPengeluaranPerKategori($month),
            'total_pengeluaran_bulanan' => $this->pengeluaranModel->getTotalPengeluaranBulanan($month),
            'total_pengeluaran_tahunan' => $this->pengeluaranModel->getTotalPengeluaranTahunan($year),
            'selected_month' => $month,
            'selected_year' => $year,
            'selected_kategori' => $kategori,
            'user_level' => $this->session->get('user_level')
        ];
        
        return view('admin/pengeluaran/laporan', $data);
    }

    /**
     * Update total pengeluaran pada program kerja
     */
    private function updateTotalPengeluaranProgram($programId)
    {
        $totalPengeluaran = $this->pengeluaranModel
            ->where('program_kerja_id', $programId)
            ->selectSum('jumlah')
            ->get()
            ->getRow()
            ->jumlah;
            
        $this->programKerjaModel->update($programId, [
            'total_pengeluaran' => $totalPengeluaran ?: 0
        ]);
    }

    /**
     * API untuk mendapatkan data pengeluaran (jika diperlukan untuk mobile app)
     */
    public function api_pengeluaran()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $limit = $this->request->getGet('limit') ?: 10;
        
        $pengeluaran = $this->pengeluaranModel
            ->orderBy('tanggal', 'DESC')
            ->limit($limit);
            
        if ($startDate && $endDate) {
            $pengeluaran->where('tanggal >=', $startDate)
                       ->where('tanggal <=', $endDate);
        }
        
        $data = $pengeluaran->findAll();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data,
            'total' => count($data)
        ]);
    }
}
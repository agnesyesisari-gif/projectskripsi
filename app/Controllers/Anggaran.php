<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AnggaranModel;
use App\Models\ProgramKerjaModel;
use App\Models\KegiatanModel;

class Anggaran extends BaseController
{
    protected $anggaranModel;
    protected $programKerjaModel;
    protected $kegiatanModel;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->anggaranModel = new AnggaranModel();
        $this->programKerjaModel = new ProgramKerjaModel();
        $this->kegiatanModel = new KegiatanModel();
        $this->session = session();
        $this->validation = \Config\Services::validation();
    }

    /**
     * Menampilkan daftar anggaran
     */
    public function index()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Konfigurasi pagination
        $perPage = 10;
        $currentPage = $this->request->getVar('page_anggaran') ? $this->request->getVar('page_anggaran') : 1;
        
        // Get data dengan join ke program kerja dan kegiatan
        $data = [
            'title' => 'Manajemen Anggaran',
            'anggaran' => $this->anggaranModel->getAnggaranWithProgram(),
            'pager' => $this->anggaranModel->pager,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalRows' => $this->anggaranModel->countAllResults(),
            'user_role' => $this->session->get('role'),
        ];

        return view('anggaran/index', $data);
    }

    /**
     * Menampilkan form tambah anggaran
     */
    public function create()
    {
        // Cek apakah user sudah login dan memiliki akses
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa menambah anggaran
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/anggaran')
                ->with('error', 'Anda tidak memiliki akses untuk menambah anggaran');
        }

        $data = [
            'title' => 'Tambah Anggaran',
            'program_kerja' => $this->programKerjaModel->findAll(),
            'kegiatan' => $this->kegiatanModel->findAll(),
            'validation' => $this->validation,
            'tahun_anggaran' => date('Y'),
        ];

        return view('anggaran/create', $data);
    }

    /**
     * Menyimpan data anggaran baru
     */
    public function store()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa menyimpan anggaran
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/anggaran')
                ->with('error', 'Anda tidak memiliki akses untuk menambah anggaran');
        }

        // Validasi input
        $rules = [
            'program_id' => 'required|numeric',
            'kegiatan_id' => 'permit_empty|numeric',
            'nama_anggaran' => 'required|min_length[3]|max_length[255]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'tahun_anggaran' => 'required|numeric|exact_length[4]',
            'periode' => 'required|in_list[tahunan,semester,triwulan,bulanan]',
            'status' => 'required|in_list[rencana,diajukan,disetujui,ditolak,realisasi]',
            'keterangan' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Generate kode anggaran
        $kodeAnggaran = $this->generateKodeAnggaran();

        // Data untuk disimpan
        $data = [
            'kode_anggaran' => $kodeAnggaran,
            'program_id' => $this->request->getVar('program_id'),
            'kegiatan_id' => $this->request->getVar('kegiatan_id') ?: null,
            'nama_anggaran' => $this->request->getVar('nama_anggaran'),
            'jumlah' => str_replace('.', '', $this->request->getVar('jumlah')),
            'tahun_anggaran' => $this->request->getVar('tahun_anggaran'),
            'periode' => $this->request->getVar('periode'),
            'status' => $this->request->getVar('status'),
            'keterangan' => $this->request->getVar('keterangan'),
            'created_by' => $this->session->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Simpan ke database
        if ($this->anggaranModel->save($data)) {
            return redirect()->to('/anggaran')
                ->with('success', 'Data anggaran berhasil ditambahkan');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan data anggaran');
        }
    }

    /**
     * Menampilkan detail anggaran
     */
    public function show($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $anggaran = $this->anggaranModel->getAnggaranDetail($id);
        
        if (!$anggaran) {
            return redirect()->to('/anggaran')
                ->with('error', 'Data anggaran tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Anggaran',
            'anggaran' => $anggaran,
            'user_role' => $this->session->get('role'),
        ];

        return view('anggaran/show', $data);
    }

    /**
     * Menampilkan form edit anggaran
     */
    public function edit($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mengedit anggaran
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/anggaran')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit anggaran');
        }

        $anggaran = $this->anggaranModel->find($id);
        
        if (!$anggaran) {
            return redirect()->to('/anggaran')
                ->with('error', 'Data anggaran tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Anggaran',
            'anggaran' => $anggaran,
            'program_kerja' => $this->programKerjaModel->findAll(),
            'kegiatan' => $this->kegiatanModel->findAll(),
            'validation' => $this->validation,
        ];

        return view('anggaran/edit', $data);
    }

    /**
     * Mengupdate data anggaran
     */
    public function update($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mengupdate anggaran
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/anggaran')
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate anggaran');
        }

        // Validasi input
        $rules = [
            'program_id' => 'required|numeric',
            'kegiatan_id' => 'permit_empty|numeric',
            'nama_anggaran' => 'required|min_length[3]|max_length[255]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'tahun_anggaran' => 'required|numeric|exact_length[4]',
            'periode' => 'required|in_list[tahunan,semester,triwulan,bulanan]',
            'status' => 'required|in_list[rencana,diajukan,disetujui,ditolak,realisasi]',
            'keterangan' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Data untuk diupdate
        $data = [
            'program_id' => $this->request->getVar('program_id'),
            'kegiatan_id' => $this->request->getVar('kegiatan_id') ?: null,
            'nama_anggaran' => $this->request->getVar('nama_anggaran'),
            'jumlah' => str_replace('.', '', $this->request->getVar('jumlah')),
            'tahun_anggaran' => $this->request->getVar('tahun_anggaran'),
            'periode' => $this->request->getVar('periode'),
            'status' => $this->request->getVar('status'),
            'keterangan' => $this->request->getVar('keterangan'),
            'updated_by' => $this->session->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update ke database
        if ($this->anggaranModel->update($id, $data)) {
            return redirect()->to('/anggaran')
                ->with('success', 'Data anggaran berhasil diperbarui');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data anggaran');
        }
    }

    /**
     * Menghapus data anggaran
     */
    public function delete($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin yang bisa menghapus anggaran
        if ($this->session->get('role') !== 'admin') {
            return redirect()->to('/anggaran')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus anggaran');
        }

        $anggaran = $this->anggaranModel->find($id);
        
        if (!$anggaran) {
            return redirect()->to('/anggaran')
                ->with('error', 'Data anggaran tidak ditemukan');
        }

        // Cek apakah anggaran sudah digunakan
        if ($anggaran['status'] === 'realisasi') {
            return redirect()->to('/anggaran')
                ->with('error', 'Tidak dapat menghapus anggaran yang sudah direalisasikan');
        }

        if ($this->anggaranModel->delete($id)) {
            return redirect()->to('/anggaran')
                ->with('success', 'Data anggaran berhasil dihapus');
        } else {
            return redirect()->to('/anggaran')
                ->with('error', 'Gagal menghapus data anggaran');
        }
    }

    /**
     * Menampilkan laporan anggaran
     */
    public function laporan()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $tahun = $this->request->getVar('tahun') ?: date('Y');
        $periode = $this->request->getVar('periode') ?: 'tahunan';
        $status = $this->request->getVar('status') ?: '';

        // Get data laporan
        $laporan = $this->anggaranModel->getLaporanAnggaran($tahun, $periode, $status);

        $data = [
            'title' => 'Laporan Anggaran',
            'laporan' => $laporan,
            'tahun' => $tahun,
            'periode' => $periode,
            'status' => $status,
            'total_anggaran' => array_sum(array_column($laporan, 'jumlah')),
            'user_role' => $this->session->get('role'),
        ];

        return view('anggaran/laporan', $data);
    }

    /**
     * Export laporan ke PDF
     */
    public function exportPdf()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $tahun = $this->request->getVar('tahun') ?: date('Y');
        $periode = $this->request->getVar('periode') ?: 'tahunan';

        // Get data laporan
        $laporan = $this->anggaranModel->getLaporanAnggaran($tahun, $periode);
        $totalAnggaran = array_sum(array_column($laporan, 'jumlah'));

        // Load library PDF
        $dompdf = new \Dompdf\Dompdf();
        
        $data = [
            'laporan' => $laporan,
            'tahun' => $tahun,
            'periode' => $periode,
            'total_anggaran' => $totalAnggaran,
            'tanggal_cetak' => date('d-m-Y H:i:s'),
        ];

        $html = view('anggaran/export_pdf', $data);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = "Laporan_Anggaran_{$tahun}_{$periode}.pdf";
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    /**
     * Mengubah status anggaran
     */
    public function updateStatus($id, $status)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mengubah status
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/anggaran')
                ->with('error', 'Anda tidak memiliki akses untuk mengubah status anggaran');
        }

        $validStatus = ['rencana', 'diajukan', 'disetujui', 'ditolak', 'realisasi'];
        
        if (!in_array($status, $validStatus)) {
            return redirect()->to('/anggaran')
                ->with('error', 'Status tidak valid');
        }

        $data = [
            'status' => $status,
            'updated_by' => $this->session->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'disetujui') {
            $data['tanggal_persetujuan'] = date('Y-m-d H:i:s');
        } elseif ($status === 'realisasi') {
            $data['tanggal_realisasi'] = date('Y-m-d H:i:s');
        }

        if ($this->anggaranModel->update($id, $data)) {
            return redirect()->to('/anggaran')
                ->with('success', 'Status anggaran berhasil diubah menjadi ' . $status);
        } else {
            return redirect()->to('/anggaran')
                ->with('error', 'Gagal mengubah status anggaran');
        }
    }

    /**
     * Dashboard ringkasan anggaran
     */
    public function dashboard()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $tahun = date('Y');
        
        $data = [
            'title' => 'Dashboard Anggaran',
            'total_anggaran_tahun_ini' => $this->anggaranModel->getTotalAnggaranByYear($tahun),
            'anggaran_disetujui' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'disetujui'),
            'anggaran_realisasi' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'realisasi'),
            'anggaran_per_program' => $this->anggaranModel->getAnggaranByProgram($tahun),
            'anggaran_per_periode' => $this->anggaranModel->getAnggaranByPeriode($tahun),
            'tahun' => $tahun,
            'user_role' => $this->session->get('role'),
        ];

        return view('anggaran/dashboard', $data);
    }

    /**
     * Generate kode anggaran otomatis
     */
    private function generateKodeAnggaran()
    {
        $tahun = date('Y');
        $prefix = 'ANG-' . $tahun . '-';
        
        // Cari kode terakhir
        $lastKode = $this->anggaranModel
            ->like('kode_anggaran', $prefix)
            ->orderBy('kode_anggaran', 'DESC')
            ->first();

        if ($lastKode) {
            $lastNumber = (int) substr($lastKode['kode_anggaran'], strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * AJAX: Get kegiatan berdasarkan program
     */
    public function getKegiatanByProgram($programId)
    {
        if (!$this->session->get('logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $kegiatan = $this->kegiatanModel
            ->where('program_id', $programId)
            ->findAll();

        return $this->response->setJSON($kegiatan);
    }

    /**
     * AJAX: Get total anggaran by status
     */
    public function getTotalByStatus()
    {
        if (!$this->session->get('logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $tahun = $this->request->getVar('tahun') ?: date('Y');
        
        $data = [
            'rencana' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'rencana'),
            'diajukan' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'diajukan'),
            'disetujui' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'disetujui'),
            'ditolak' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'ditolak'),
            'realisasi' => $this->anggaranModel->getTotalAnggaranByStatus($tahun, 'realisasi'),
        ];

        return $this->response->setJSON($data);
    }
}
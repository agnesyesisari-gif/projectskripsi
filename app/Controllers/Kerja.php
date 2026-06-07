<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KegiatanModel;

class Kerja extends BaseController
{
    protected $kegiatanModel;
    protected $validation;
    
    public function __construct()
    {
        $this->kegiatanModel = new KegiatanModel();
        $this->validation = \Config\Services::validation();
    }
    
    /**
     * Menampilkan semua kegiatan berdasarkan kategori
     */
    public function index()
    {
        $data = [
            'title' => 'Program Kerja Pelayanan Gereja',
            'kegiatan' => $this->kegiatanModel->getAllWithKategori(),
            'kategori' => $this->kegiatanModel->getKategoriOptions(),
            'activePage' => 'kerja'
        ];
        
        return view('kerja/index', $data);
    }
    
    /**
     * Menampilkan kegiatan berdasarkan kategori
     */
    public function byKategori($kategoriId)
    {
        $data = [
            'title' => 'Program Kerja per Kategori',
            'kegiatan' => $this->kegiatanModel->getByKategori($kategoriId),
            'kategori' => $this->kegiatanModel->getKategoriById($kategoriId),
            'activePage' => 'kerja'
        ];
        
        return view('kerja/index', $data);
    }
    
    /**
     * Menampilkan form tambah kegiatan
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Program Kerja',
            'validation' => $this->validation,
            'kategori' => $this->kegiatanModel->getKategoriOptions(),
            'pelayan' => $this->kegiatanModel->getPelayanOptions(),
            'activePage' => 'kerja'
        ];
        
        return view('kerja/create', $data);
    }
    
    /**
     * Menyimpan data kegiatan baru
     */
    public function store()
    {
        // Validasi input
        $rules = [
            'nama_proker' => 'required|min_length[3]|max_length[255]',
            'kategori_id' => 'required|numeric',
            'deskripsi' => 'required|min_length[10]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'anggaran' => 'numeric',
            'penanggung_jawab_id' => 'required|numeric'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Simpan data
        $data = [
            'nama_proker' => $this->request->getPost('nama_proker'),
            'kategori_id' => $this->request->getPost('kategori_id'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'lokasi' => $this->request->getPost('lokasi'),
            'anggaran' => $this->request->getPost('anggaran'),
            'penanggung_jawab_id' => $this->request->getPost('penanggung_jawab_id'),
            'status' => 'draft',
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->kegiatanModel->save($data)) {
            return redirect()->to('/kerja')->with('success', 'Program kerja berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan program kerja.');
        }
    }
    
    /**
     * Menampilkan detail kegiatan
     */
    public function show($id)
    {
        $kegiatan = $this->kegiatanModel->getWithDetail($id);
        
        if (!$kegiatan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        $data = [
            'title' => 'Detail Program Kerja',
            'kegiatan' => $kegiatan,
            'activePage' => 'kerja'
        ];
        
        return view('kerja/view', $data);
    }
    
    /**
     * Menampilkan form edit kegiatan
     */
    public function edit($id)
    {
        $kegiatan = $this->kegiatanModel->find($id);
        
        if (!$kegiatan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        $data = [
            'title' => 'Edit Program Kerja',
            'validation' => $this->validation,
            'kegiatan' => $kegiatan,
            'kategori' => $this->kegiatanModel->getKategoriOptions(),
            'pelayan' => $this->kegiatanModel->getPelayanOptions(),
            'activePage' => 'kerja'
        ];
        
        return view('kerja/edit', $data);
    }
    
    /**
     * Mengupdate data kegiatan
     */
    public function update($id)
    {
        // Cek apakah kegiatan ada
        $kegiatan = $this->kegiatanModel->find($id);
        if (!$kegiatan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Validasi
        $rules = [
            'nama_proker' => 'required|min_length[3]|max_length[255]',
            'kategori_id' => 'required|numeric',
            'deskripsi' => 'required|min_length[10]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'anggaran' => 'numeric',
            'penanggung_jawab_id' => 'required|numeric',
            'status' => 'required|in_list[draft,sedang berjalan,selesai,dibatalkan]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Update data
        $data = [
            'nama_proker' => $this->request->getPost('nama_proker'),
            'kategori_id' => $this->request->getPost('kategori_id'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'lokasi' => $this->request->getPost('lokasi'),
            'anggaran' => $this->request->getPost('anggaran'),
            'penanggung_jawab_id' => $this->request->getPost('penanggung_jawab_id'),
            'status' => $this->request->getPost('status'),
            'updated_by' => session()->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->kegiatanModel->update($id, $data)) {
            return redirect()->to('/kerja')->with('success', 'Program kerja berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui program kerja.');
        }
    }
    
    /**
     * Menghapus kegiatan
     */
    public function delete($id)
    {
        $kegiatan = $this->kegiatanModel->find($id);
        
        if (!$kegiatan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        if ($this->kegiatanModel->delete($id)) {
            return redirect()->to('/kerja')->with('success', 'Program kerja berhasil dihapus.');
        } else {
            return redirect()->to('/kerja')->with('error', 'Gagal menghapus program kerja.');
        }
    }
    
    /**
     * API untuk mendapatkan data kegiatan (JSON)
     */
    public function apiKegiatan($kategoriId = null)
    {
        $data = $kategoriId 
            ? $this->kegiatanModel->getByKategori($kategoriId)
            : $this->kegiatanModel->getAllWithKategori();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * Export data ke PDF
     */
    public function exportPdf($kategoriId = null)
    {
        $data = [
            'kegiatan' => $kategoriId 
                ? $this->kegiatanModel->getByKategori($kategoriId)
                : $this->kegiatanModel->getAllWithKategori(),
            'title' => 'Laporan Program Kerja Pelayanan Gereja',
            'tanggal_cetak' => date('d-m-Y H:i:s')
        ];
        
        // Load view untuk PDF
        $html = view('kerja/export_pdf', $data);
        
        // Menggunakan Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream('program-kerja-gereja.pdf', ['Attachment' => 0]);
    }
    
    /**
     * Update status kegiatan
     */
    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        
        $allowedStatus = ['draft', 'on_progress', 'completed', 'cancelled'];
        
        if (!in_array($status, $allowedStatus)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status tidak valid'
            ]);
        }
        
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->kegiatanModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status berhasil diperbarui'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal memperbarui status'
        ]);
    }
    
    /**
     * Dashboard ringkasan
     */
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard Program Kerja',
            'total_kegiatan' => $this->kegiatanModel->countAll(),
            'kegiatan_by_kategori' => $this->kegiatanModel->getCountByKategori(),
            'kegiatan_by_status' => $this->kegiatanModel->getCountByStatus(),
            'kegiatan_terbaru' => $this->kegiatanModel->getLatest(5),
            'activePage' => 'dashboard'
        ];
        
        return view('kerja/dashboard', $data);
    }
}
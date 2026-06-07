<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarisModel;
use App\Models\KategoriInventarisModel;
use App\Models\LokasiModel;
use App\Models\UserModel;

class Inventaris extends BaseController
{
    protected $inventarisModel;
    protected $kategoriModel;
    protected $lokasiModel;
    protected $userModel;
    protected $session;
    protected $validation;
    protected $helpers = ['form', 'url', 'date'];

    public function __construct()
    {
        $this->inventarisModel = new InventarisModel();
        $this->kategoriModel = new KategoriInventarisModel();
        $this->lokasiModel = new LokasiModel();
        $this->userModel = new UserModel();
        $this->session = session();
        $this->validation = \Config\Services::validation();
    }

    /**
     * Menampilkan daftar inventaris
     */
    public function index()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Konfigurasi pagination
        $perPage = 15;
        $currentPage = $this->request->getVar('page_inventaris') ? $this->request->getVar('page_inventaris') : 1;
        
        // Filter pencarian
        $keyword = $this->request->getVar('keyword');
        $kategori = $this->request->getVar('kategori');
        $status = $this->request->getVar('status');
        
        // Get data dengan filter
        $inventaris = $this->inventarisModel->getInventarisWithRelations($keyword, $kategori, $status);
        
        $data = [
            'title' => 'Manajemen Inventaris Gereja',
            'inventaris' => $inventaris->paginate($perPage, 'inventaris'),
            'pager' => $this->inventarisModel->pager,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalRows' => $inventaris->countAllResults(false),
            'kategori_list' => $this->kategoriModel->findAll(),
            'keyword' => $keyword,
            'kategori_filter' => $kategori,
            'status_filter' => $status,
            'user_role' => $this->session->get('role'),
        ];

        return view('inventaris/index', $data);
    }

    /**
     * Menampilkan form tambah inventaris
     */
    public function create()
    {
        // Cek apakah user sudah login dan memiliki akses
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa menambah inventaris
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris')
                ->with('error', 'Anda tidak memiliki akses untuk menambah data inventaris');
        }

        $data = [
            'title' => 'Tambah Data Inventaris',
            'kategori_list' => $this->kategoriModel->findAll(),
            'lokasi_list' => $this->lokasiModel->findAll(),
            'user_list' => $this->Model->where('status', 'aktif')->findAll(),
            'validation' => $this->validation,
        ];

        return view('inventaris/create', $data);
    }

    /**
     * Menyimpan data inventaris baru
     */
    public function store()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa menyimpan inventaris
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris')
                ->with('error', 'Anda tidak memiliki akses untuk menambah data inventaris');
        }

        // Validasi input
        $rules = [
            'kode_barang' => 'required|is_unique[inventaris.kode_barang]|max_length[50]',
            'nama_barang' => 'required|min_length[3]|max_length[255]',
            'kategori_id' => 'required|numeric',
            'merk' => 'permit_empty|max_length[100]',
            'tipe' => 'permit_empty|max_length[100]',
            'no_seri' => 'permit_empty|max_length[100]',
            'tahun_pembelian' => 'permit_empty|numeric|exact_length[4]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'satuan' => 'required|max_length[20]',
            'kondisi' => 'required|in_list[baik,rusak_ringan,rusak_berat,perbaikan]',
            'lokasi_id' => 'required|numeric',
            'pengguna_id' => 'permit_empty|numeric',
            'sumber_dana' => 'permit_empty|max_length[100]',
            'harga_beli' => 'permit_empty|numeric',
            'nilai_residu' => 'permit_empty|numeric',
            'masa_manfaat' => 'permit_empty|numeric',
            'keterangan' => 'permit_empty|max_length[500]',
            'foto_barang' => 'permit_empty|uploaded[foto_barang]|max_size[foto_barang,2048]|is_image[foto_barang]|mime_in[foto_barang,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Handle upload foto
        $fotoBarang = $this->request->getFile('foto_barang');
        $namaFoto = 'default.jpg';
        
        if ($fotoBarang && $fotoBarang->isValid() && !$fotoBarang->hasMoved()) {
            $namaFoto = $fotoBarang->getRandomName();
            $fotoBarang->move('uploads/inventaris', $namaFoto);
            
            // Resize image jika perlu
            $this->resizeImage('uploads/inventaris/' . $namaFoto, 800, 600);
        }

        // Data untuk disimpan
        $data = [
            'kode_barang' => strtoupper($this->request->getVar('kode_barang')),
            'nama_barang' => $this->request->getVar('nama_barang'),
            'kategori_id' => $this->request->getVar('kategori_id'),
            'merk' => $this->request->getVar('merk'),
            'tipe' => $this->request->getVar('tipe'),
            'no_seri' => $this->request->getVar('no_seri'),
            'tahun_pembelian' => $this->request->getVar('tahun_pembelian') ?: null,
            'jumlah' => $this->request->getVar('jumlah'),
            'satuan' => $this->request->getVar('satuan'),
            'kondisi' => $this->request->getVar('kondisi'),
            'lokasi_id' => $this->request->getVar('lokasi_id'),
            'pengguna_id' => $this->request->getVar('pengguna_id') ?: null,
            'sumber_dana' => $this->request->getVar('sumber_dana'),
            'harga_beli' => $this->request->getVar('harga_beli') ? str_replace('.', '', $this->request->getVar('harga_beli')) : null,
            'nilai_residu' => $this->request->getVar('nilai_residu') ? str_replace('.', '', $this->request->getVar('nilai_residu')) : null,
            'masa_manfaat' => $this->request->getVar('masa_manfaat'),
            'keterangan' => $this->request->getVar('keterangan'),
            'foto_barang' => $namaFoto,
            'status' => 'tersedia',
            'created_by' => $this->session->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Hitung nilai penyusutan jika ada harga beli dan masa manfaat
        if ($data['harga_beli'] && $data['masa_manfaat'] && $data['nilai_residu']) {
            $data['penyusutan_per_tahun'] = ($data['harga_beli'] - $data['nilai_residu']) / $data['masa_manfaat'];
        }

        // Simpan ke database
        if ($this->inventarisModel->save($data)) {
            return redirect()->to('/inventaris')
                ->with('success', 'Data inventaris berhasil ditambahkan');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan data inventaris');
        }
    }

    /**
     * Menampilkan detail inventaris
     */
    public function show($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $inventaris = $this->inventarisModel->getInventarisDetail($id);
        
        if (!$inventaris) {
            return redirect()->to('/inventaris')
                ->with('error', 'Data inventaris tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Inventaris',
            'inventaris' => $inventaris,
            'riwayat_pemeliharaan' => $this->inventarisModel->getRiwayatPemeliharaan($id),
            'riwayat_peminjaman' => $this->inventarisModel->getRiwayatPeminjaman($id),
            'user_role' => $this->session->get('role'),
        ];

        return view('inventaris/show', $data);
    }

    /**
     * Menampilkan form edit inventaris
     */
    public function edit($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mengedit inventaris
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit data inventaris');
        }

        $inventaris = $this->inventarisModel->find($id);
        
        if (!$inventaris) {
            return redirect()->to('/inventaris')
                ->with('error', 'Data inventaris tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Data Inventaris',
            'inventaris' => $inventaris,
            'kategori_list' => $this->kategoriModel->findAll(),
            'lokasi_list' => $this->lokasiModel->findAll(),
            'pengguna_list' => $this->penggunaModel->where('status', 'aktif')->findAll(),
            'validation' => $this->validation,
        ];

        return view('inventaris/edit', $data);
    }

    /**
     * Mengupdate data inventaris
     */
    public function update($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mengupdate inventaris
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris')
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate data inventaris');
        }

        // Cek kode barang unik (kecuali untuk data ini)
        $inventaris = $this->inventarisModel->find($id);
        $kodeBarangRules = $inventaris['kode_barang'] === $this->request->getVar('kode_barang') 
            ? 'required|max_length[50]' 
            : 'required|is_unique[inventaris.kode_barang]|max_length[50]';

        // Validasi input
        $rules = [
            'kode_barang' => $kodeBarangRules,
            'nama_barang' => 'required|min_length[3]|max_length[255]',
            'kategori_id' => 'required|numeric',
            'merk' => 'permit_empty|max_length[100]',
            'tipe' => 'permit_empty|max_length[100]',
            'no_seri' => 'permit_empty|max_length[100]',
            'tahun_pembelian' => 'permit_empty|numeric|exact_length[4]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'satuan' => 'required|max_length[20]',
            'kondisi' => 'required|in_list[baik,rusak_ringan,rusak_berat,perbaikan]',
            'lokasi_id' => 'required|numeric',
            'pengguna_id' => 'permit_empty|numeric',
            'sumber_dana' => 'permit_empty|max_length[100]',
            'harga_beli' => 'permit_empty|numeric',
            'keterangan' => 'permit_empty|max_length[500]',
            'foto_barang' => 'permit_empty|uploaded[foto_barang]|max_size[foto_barang,2048]|is_image[foto_barang]|mime_in[foto_barang,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Handle upload foto baru
        $fotoBarang = $this->request->getFile('foto_barang');
        $namaFoto = $inventaris['foto_barang'];
        
        if ($fotoBarang && $fotoBarang->isValid() && !$fotoBarang->hasMoved()) {
            // Hapus foto lama jika bukan default
            if ($namaFoto !== 'default.jpg') {
                $fotoLama = 'uploads/inventaris/' . $namaFoto;
                if (file_exists($fotoLama)) {
                    unlink($fotoLama);
                }
            }
            
            $namaFoto = $fotoBarang->getRandomName();
            $fotoBarang->move('uploads/inventaris', $namaFoto);
            
            // Resize image jika perlu
            $this->resizeImage('uploads/inventaris/' . $namaFoto, 800, 600);
        }

        // Data untuk diupdate
        $data = [
            'kode_barang' => strtoupper($this->request->getVar('kode_barang')),
            'nama_barang' => $this->request->getVar('nama_barang'),
            'kategori_id' => $this->request->getVar('kategori_id'),
            'merk' => $this->request->getVar('merk'),
            'tipe' => $this->request->getVar('tipe'),
            'no_seri' => $this->request->getVar('no_seri'),
            'tahun_pembelian' => $this->request->getVar('tahun_pembelian') ?: null,
            'jumlah' => $this->request->getVar('jumlah'),
            'satuan' => $this->request->getVar('satuan'),
            'kondisi' => $this->request->getVar('kondisi'),
            'lokasi_id' => $this->request->getVar('lokasi_id'),
            'pengguna_id' => $this->request->getVar('pengguna_id') ?: null,
            'sumber_dana' => $this->request->getVar('sumber_dana'),
            'harga_beli' => $this->request->getVar('harga_beli') ? str_replace('.', '', $this->request->getVar('harga_beli')) : null,
            'keterangan' => $this->request->getVar('keterangan'),
            'foto_barang' => $namaFoto,
            'updated_by' => $this->session->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update ke database
        if ($this->inventarisModel->update($id, $data)) {
            return redirect()->to('/inventaris')
                ->with('success', 'Data inventaris berhasil diperbarui');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data inventaris');
        }
    }

    /**
     * Menghapus data inventaris
     */
    public function delete($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin yang bisa menghapus inventaris
        if ($this->session->get('role') !== 'admin') {
            return redirect()->to('/inventaris')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus data inventaris');
        }

        $inventaris = $this->inventarisModel->find($id);
        
        if (!$inventaris) {
            return redirect()->to('/inventaris')
                ->with('error', 'Data inventaris tidak ditemukan');
        }

        // Cek apakah inventaris sedang dipinjam
        if ($inventaris['status'] === 'dipinjam') {
            return redirect()->to('/inventaris')
                ->with('error', 'Tidak dapat menghapus inventaris yang sedang dipinjam');
        }

        // Hapus foto jika bukan default
        if ($inventaris['foto_barang'] !== 'default.jpg') {
            $fotoPath = 'uploads/inventaris/' . $inventaris['foto_barang'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        if ($this->inventarisModel->delete($id)) {
            return redirect()->to('/inventaris')
                ->with('success', 'Data inventaris berhasil dihapus');
        } else {
            return redirect()->to('/inventaris')
                ->with('error', 'Gagal menghapus data inventaris');
        }
    }

    /**
     * Mengubah status inventaris (tersedia/dipinjam/rusak/hilang)
     */
    public function updateStatus($id)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mengubah status
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris')
                ->with('error', 'Anda tidak memiliki akses untuk mengubah status inventaris');
        }

        $status = $this->request->getVar('status');
        $alasan = $this->request->getVar('alasan');
        $kondisi = $this->request->getVar('kondisi');

        $validStatus = ['tersedia', 'dipinjam', 'rusak', 'hilang', 'perbaikan'];
        
        if (!in_array($status, $validStatus)) {
            return redirect()->to('/inventaris')
                ->with('error', 'Status tidak valid');
        }

        $data = [
            'status' => $status,
            'updated_by' => $this->session->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Jika ada perubahan kondisi, update juga
        if ($kondisi) {
            $data['kondisi'] = $kondisi;
        }

        if ($this->inventarisModel->update($id, $data)) {
            // Catat riwayat perubahan status
            $riwayatData = [
                'inventaris_id' => $id,
                'status_sebelum' => $this->request->getVar('status_sebelum'),
                'status_sesudah' => $status,
                'alasan' => $alasan,
                'created_by' => $this->session->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ];
            
            // Simpan ke tabel riwayat_status
            $this->inventarisModel->simpanRiwayatStatus($riwayatData);

            return redirect()->to('/inventaris/show/' . $id)
                ->with('success', 'Status inventaris berhasil diubah');
        } else {
            return redirect()->to('/inventaris/show/' . $id)
                ->with('error', 'Gagal mengubah status inventaris');
        }
    }

    /**
     * Manajemen peminjaman inventaris
     */
    public function peminjaman()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $perPage = 15;
        $currentPage = $this->request->getVar('page_peminjaman') ? $this->request->getVar('page_peminjaman') : 1;
        
        $status = $this->request->getVar('status') ?: 'semua';
        $peminjaman = $this->inventarisModel->getDataPeminjaman($status);

        $data = [
            'title' => 'Manajemen Peminjaman Inventaris',
            'peminjaman' => $peminjaman->paginate($perPage, 'peminjaman'),
            'pager' => $this->inventarisModel->pager,
            'currentPage' => $currentPage,
            'status_filter' => $status,
            'user_role' => $this->session->get('role'),
        ];

        return view('inventaris/peminjaman', $data);
    }

    /**
     * Form peminjaman inventaris
     */
    public function createPeminjaman()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin, bendahara, dan sekretaris yang bisa meminjamkan
        if (!in_array($this->session->get('role'), ['admin', 'bendahara', 'sekretaris'])) {
            return redirect()->to('/inventaris/peminjaman')
                ->with('error', 'Anda tidak memiliki akses untuk meminjamkan inventaris');
        }

        $data = [
            'title' => 'Form Peminjaman Inventaris',
            'inventaris_tersedia' => $this->inventarisModel->where('status', 'tersedia')->findAll(),
            'peminjam_list' => $this->penggunaModel->where('status', 'aktif')->findAll(),
            'validation' => $this->validation,
        ];

        return view('inventaris/create_peminjaman', $data);
    }

    /**
     * Proses peminjaman inventaris
     */
    public function storePeminjaman()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin, bendahara, dan sekretaris yang bisa meminjamkan
        if (!in_array($this->session->get('role'), ['admin', 'bendahara', 'sekretaris'])) {
            return redirect()->to('/inventaris/peminjaman')
                ->with('error', 'Anda tidak memiliki akses untuk meminjamkan inventaris');
        }

        // Validasi input
        $rules = [
            'inventaris_id' => 'required|numeric',
            'peminjam_id' => 'required|numeric',
            'jumlah_pinjam' => 'required|numeric|greater_than[0]',
            'tanggal_pinjam' => 'required|valid_date',
            'tanggal_kembali' => 'required|valid_date',
            'keperluan' => 'required|min_length[5]|max_length[500]',
            'jaminan' => 'permit_empty|max_length[100]',
            'keterangan' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Cek ketersediaan barang
        $inventaris = $this->inventarisModel->find($this->request->getVar('inventaris_id'));
        $jumlahPinjam = $this->request->getVar('jumlah_pinjam');
        
        if ($inventaris['jumlah'] < $jumlahPinjam) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jumlah pinjam melebihi stok yang tersedia');
        }

        // Generate kode peminjaman
        $kodePeminjaman = $this->generateKodePeminjaman();

        // Data peminjaman
        $peminjamanData = [
            'kode_peminjaman' => $kodePeminjaman,
            'inventaris_id' => $this->request->getVar('inventaris_id'),
            'peminjam_id' => $this->request->getVar('peminjam_id'),
            'jumlah_pinjam' => $jumlahPinjam,
            'tanggal_pinjam' => $this->request->getVar('tanggal_pinjam'),
            'tanggal_kembali' => $this->request->getVar('tanggal_kembali'),
            'keperluan' => $this->request->getVar('keperluan'),
            'keterangan' => $this->request->getVar('keterangan'),
            'status_peminjaman' => 'dipinjam',
            'created_by' => $this->session->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Update stok inventaris
        $updateInventaris = [
            'jumlah' => $inventaris['jumlah'] - $jumlahPinjam,
            'status' => $inventaris['jumlah'] - $jumlahPinjam == 0 ? 'dipinjam' : 'tersedia',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Mulai transaksi database
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Simpan data peminjaman
            $this->inventarisModel->simpanPeminjaman($peminjamanData);
            
            // Update inventaris
            $this->inventarisModel->update($inventaris['id'], $updateInventaris);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan data peminjaman');
            }

            return redirect()->to('/inventaris/peminjaman')
                ->with('success', 'Peminjaman inventaris berhasil dicatat');
                
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Proses pengembalian inventaris
     */
    public function prosesPengembalian($peminjamanId)
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin, bendahara, dan sekretaris yang bisa memproses pengembalian
        if (!in_array($this->session->get('role'), ['admin', 'bendahara', 'sekretaris'])) {
            return redirect()->to('/inventaris/peminjaman')
                ->with('error', 'Anda tidak memiliki akses untuk memproses pengembalian');
        }

        $peminjaman = $this->inventarisModel->getPeminjamanById($peminjamanId);
        
        if (!$peminjaman || $peminjaman['status_peminjaman'] !== 'dipinjam') {
            return redirect()->to('/inventaris/peminjaman')
                ->with('error', 'Data peminjaman tidak valid');
        }

        // Validasi input
        $rules = [
            'kondisi_kembali' => 'required|in_list[baik,rusak_ringan,rusak_berat]',
            'keterangan_pengembalian' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $kondisiKembali = $this->request->getVar('kondisi_kembali');
        $keterangan = $this->request->getVar('keterangan_pengembalian');

        // Data update peminjaman
        $updatePeminjaman = [
            'tanggal_dikembalikan' => date('Y-m-d H:i:s'),
            'kondisi_kembali' => $kondisiKembali,
            'keterangan_pengembalian' => $keterangan,
            'status_peminjaman' => 'dikembalikan',
            'updated_by' => $this->session->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Data update inventaris
        $inventaris = $this->inventarisModel->find($peminjaman['inventaris_id']);
        $updateInventaris = [
            'jumlah' => $inventaris['jumlah'] + $peminjaman['jumlah_pinjam'],
            'kondisi' => $kondisiKembali,
            'status' => 'tersedia',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Mulai transaksi database
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update peminjaman
            $this->inventarisModel->updatePeminjaman($peminjamanId, $updatePeminjaman);
            
            // Update inventaris
            $this->inventarisModel->update($peminjaman['inventaris_id'], $updateInventaris);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal memproses pengembalian');
            }

            return redirect()->to('/inventaris/peminjaman')
                ->with('success', 'Pengembalian inventaris berhasil diproses');
                
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Manajemen pemeliharaan inventaris
     */
    public function pemeliharaan()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $perPage = 15;
        $currentPage = $this->request->getVar('page_pemeliharaan') ? $this->request->getVar('page_pemeliharaan') : 1;
        
        $status = $this->request->getVar('status') ?: 'semua';
        $pemeliharaan = $this->inventarisModel->getDataPemeliharaan($status);

        $data = [
            'title' => 'Manajemen Pemeliharaan Inventaris',
            'pemeliharaan' => $pemeliharaan->paginate($perPage, 'pemeliharaan'),
            'pager' => $this->inventarisModel->pager,
            'currentPage' => $currentPage,
            'status_filter' => $status,
            'user_role' => $this->session->get('role'),
        ];

        return view('inventaris/pemeliharaan', $data);
    }

    /**
     * Form pemeliharaan inventaris
     */
    public function createPemeliharaan()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mencatat pemeliharaan
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris/pemeliharaan')
                ->with('error', 'Anda tidak memiliki akses untuk mencatat pemeliharaan');
        }

        $data = [
            'title' => 'Form Pemeliharaan Inventaris',
            'inventaris_list' => $this->inventarisModel->whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->findAll(),
            'teknisi_list' => $this->penggunaModel->where('jabatan', 'teknisi')->findAll(),
            'validation' => $this->validation,
        ];

        return view('inventaris/create_pemeliharaan', $data);
    }

    /**
     * Proses pemeliharaan inventaris
     */
    public function storePemeliharaan()
    {
        // Cek apakah user sudah login
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        // Hanya admin dan bendahara yang bisa mencatat pemeliharaan
        if (!in_array($this->session->get('role'), ['admin', 'bendahara'])) {
            return redirect()->to('/inventaris/pemeliharaan')
                ->with('error', 'Anda tidak memiliki akses untuk mencatat pemeliharaan');
        }

        // Validasi input
        $rules = [
            'inventaris_id' => 'required|numeric',
            'jenis_pemeliharaan' => 'required|in_list[rutin,perbaikan,penggantian]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'permit_empty|valid_date',
            'teknisi_id' => 'required|numeric',
            'biaya' => 'permit_empty|numeric',
            'keterangan' => 'required|min_length[5]|max_length[500]',
            'status_pemeliharaan' => 'required|in_list[rencana,proses,selesai,batal]',
        ];    
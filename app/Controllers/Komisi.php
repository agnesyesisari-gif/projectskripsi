<?php

namespace App\Controllers;

use App\Models\KomisiModel;
use App\Models\AnggotaKomisiModel;
use App\Models\ProgramKerjaModel;
use App\Models\KegiatanModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Komisi extends ResourceController
{
    use ResponseTrait;

    protected $komisiModel;
    protected $anggotaKomisiModel;
    protected $programKerjaModel;
    protected $kegiatanModel;
    protected $userModel;

    public function __construct()
    {
        $this->komisiModel = new KomisiModel();
        $this->anggotaKomisiModel = new AnggotaKomisiModel();
        $this->programKerjaModel = new ProgramKerjaModel();
        $this->kegiatanModel = new KegiatanModel();
        $this->userModel = new UserModel();
        
        helper(['form', 'date', 'text']);
    }

    /**
     * Menampilkan daftar semua komisi
     */
    public function index()
    {
        $data = [
            'title' => 'Manajemen Komisi Gereja',
            'komisi' => $this->komisiModel->getAllKomisiWithDetails(),
            'totalKomisi' => $this->komisiModel->countAll(),
            'activeMenu' => 'komisi'
        ];

        return view('komisi/index', $data);
    }

    /**
     * Menampilkan form tambah komisi
     */
    public function new()
    {
        $data = [
            'title' => 'Tambah Komisi Baru',
            'validation' => \Config\Services::validation(),
            'ketuaOptions' => $this->userModel->getUsersByRole('pelayan'),
            'activeMenu' => 'komisi'
        ];

        return view('komisi/create', $data);
    }

    /**
     * Menyimpan komisi baru
     */
    public function create()
    {
        // Validasi input
        $rules = [
            'nama_komisi' => [
                'rules' => 'required|min_length[3]|max_length[100]|is_unique[komisi.nama_komisi]',
                'errors' => [
                    'required' => 'Nama komisi harus diisi',
                    'min_length' => 'Nama komisi minimal 3 karakter',
                    'max_length' => 'Nama komisi maksimal 100 karakter',
                    'is_unique' => 'Nama komisi sudah digunakan'
                ]
            ],
            'tugas_pokok' => [
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Tugas pokok harus diisi',
                    'min_length' => 'Tugas pokok minimal 10 karakter'
                ]
            ],
            'warna' => [
                'rules' => 'required|valid_color',
                'errors' => [
                    'required' => 'Warna identitas harus dipilih',
                    'valid_color' => 'Format warna tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Upload logo jika ada
        $logoName = null;
        $logoFile = $this->request->getFile('logo');
        
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $newName = $logoFile->getRandomName();
            $logoFile->move(FCPATH . 'uploads/komisi/logo', $newName);
            $logoName = $newName;
        }

        // Simpan data komisi
        $data = [
            'nama_komisi' => $this->request->getVar('nama_komisi'),
            'slug' => url_title($this->request->getVar('nama_komisi'), '-', true),
            'tugas_pokok' => $this->request->getVar('tugas_pokok'),
            'ketua_id' => $this->request->getVar('ketua_id') ?: null,
            'sekretaris_id' => $this->request->getVar('sekretaris_id') ?: null,
            'bendahara_id' => $this->request->getVar('bendahara_id') ?: null,
            'warna' => $this->request->getVar('warna'),
            'logo' => $logoName,
            'status' => $this->request->getVar('status') ?: 'aktif',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('user_id')
        ];

        // Simpan ke database
        if ($komisiId = $this->komisiModel->insert($data)) {
            // Tambahkan pengurus jika ada
            $this->addPengurus($komisiId, 'ketua', $data['ketua_id']);
            $this->addPengurus($komisiId, 'sekretaris', $data['sekretaris_id']);
            $this->addPengurus($komisiId, 'bendahara', $data['bendahara_id']);
            
            session()->setFlashdata('success', 'Komisi berhasil ditambahkan');
            return redirect()->to('/komisi');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan komisi');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menampilkan detail komisi
     */
    public function show($id = null)
    {
        $komisi = $this->komisiModel->getKomisiWithDetails($id);

        if (!$komisi) {
            session()->setFlashdata('error', 'Komisi tidak ditemukan');
            return redirect()->to('/komisi');
        }

        $data = [
            'title' => 'Detail Komisi: ' . $komisi['nama_komisi'],
            'komisi' => $komisi,
            'anggota' => $this->anggotaKomisiModel->getAnggotaByKomisi($id),
            'programKerja' => $this->programKerjaModel->getProgramByKomisi($id),
            'kegiatan' => $this->kegiatanModel->getKegiatanByKomisi($id, 5),
            'totalAnggota' => $this->anggotaKomisiModel->countAnggotaByKomisi($id),
            'totalProgram' => $this->programKerjaModel->countProgramByKomisi($id),
            'activeMenu' => 'komisi'
        ];

        return view('komisi/show', $data);
    }

    /**
     * Menampilkan form edit komisi
     */
    public function edit($id = null)
    {
        $komisi = $this->komisiModel->find($id);

        if (!$komisi) {
            session()->setFlashdata('error', 'Komisi tidak ditemukan');
            return redirect()->to('/komisi');
        }

        $data = [
            'title' => 'Edit Komisi: ' . $komisi['nama_komisi'],
            'komisi' => $komisi,
            'ketuaOptions' => $this->userModel->getUsersByRole('pelayan'),
            'anggotaOptions' => $this->userModel->findAll(),
            'validation' => \Config\Services::validation(),
            'activeMenu' => 'komisi'
        ];

        return view('komisi/edit', $data);
    }

    /**
     * Mengupdate data komisi
     */
    public function update($id = null)
    {
        // Cek apakah komisi ada
        $komisi = $this->komisiModel->find($id);
        if (!$komisi) {
            session()->setFlashdata('error', 'Komisi tidak ditemukan');
            return redirect()->to('/komisi');
        }

        // Validasi input
        $rules = [
            'nama_komisi' => [
                'rules' => "required|min_length[3]|max_length[100]|is_unique[komisi.nama_komisi,id,{$id}]",
                'errors' => [
                    'required' => 'Nama komisi harus diisi',
                    'min_length' => 'Nama komisi minimal 3 karakter',
                    'max_length' => 'Nama komisi maksimal 100 karakter',
                    'is_unique' => 'Nama komisi sudah digunakan'
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Upload logo baru jika ada
        $logoName = $komisi['logo'];
        $logoFile = $this->request->getFile('logo');
        
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            // Hapus logo lama jika ada
            if ($logoName && file_exists(FCPATH . 'uploads/komisi/logo/' . $logoName)) {
                unlink(FCPATH . 'uploads/komisi/logo/' . $logoName);
            }
            
            $newName = $logoFile->getRandomName();
            $logoFile->move(FCPATH . 'uploads/komisi/logo', $newName);
            $logoName = $newName;
        }

        // Update data komisi
        $data = [
            'nama_komisi' => $this->request->getVar('nama_komisi'),
            'slug' => url_title($this->request->getVar('nama_komisi'), '-', true),
            'tugas_pokok' => $this->request->getVar('tugas_pokok'),
            'ketua_id' => $this->request->getVar('ketua_id') ?: null,
            'sekretaris_id' => $this->request->getVar('sekretaris_id') ?: null,
            'bendahara_id' => $this->request->getVar('bendahara_id') ?: null,
            'warna' => $this->request->getVar('warna'),
            'logo' => $logoName,
            'status' => $this->request->getVar('status') ?: 'aktif',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session()->get('user_id')
        ];

        // Update ke database
        if ($this->komisiModel->update($id, $data)) {
            // Update pengurus
            $this->updatePengurus($id, 'ketua', $data['ketua_id']);
            $this->updatePengurus($id, 'sekretaris', $data['sekretaris_id']);
            $this->updatePengurus($id, 'bendahara', $data['bendahara_id']);
            
            session()->setFlashdata('success', 'Komisi berhasil diperbarui');
            return redirect()->to('/komisi');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui komisi');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menghapus komisi
     */
    public function delete($id = null)
    {
        $komisi = $this->komisiModel->find($id);
        
        if (!$komisi) {
            session()->setFlashdata('error', 'Komisi tidak ditemukan');
            return redirect()->to('/komisi');
        }

        // Hapus logo jika ada
        if ($komisi['logo'] && file_exists(FCPATH . 'uploads/komisi/logo/' . $komisi['logo'])) {
            unlink(FCPATH . 'uploads/komisi/logo/' . $komisi['logo']);
        }

        if ($this->komisiModel->delete($id)) {
            session()->setFlashdata('success', 'Komisi berhasil dihapus');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus komisi');
        }

        return redirect()->to('/komisi');
    }

    /**
     * Menampilkan form tambah anggota komisi
     */
    public function anggota($komisi_id)
    {
        $komisi = $this->komisiModel->find($komisi_id);
        
        if (!$komisi) {
            session()->setFlashdata('error', 'Komisi tidak ditemukan');
            return redirect()->to('/komisi');
        }

        $data = [
            'title' => 'Tambah Anggota Komisi: ' . $komisi['nama_komisi'],
            'komisi' => $komisi,
            'users' => $this->userModel->getAvailableUsersForKomisi($komisi_id),
            'jabatanOptions' => $this->getJabatanOptions(),
            'validation' => \Config\Services::validation(),
            'activeMenu' => 'komisi'
        ];

        return view('komisi/anggota/create', $data);
    }

    /**
     * Menyimpan anggota komisi
     */
    public function simpanAnggota($komisi_id)
    {
        // Validasi
        $rules = [
            'user_id' => [
                'rules' => 'required|numeric|is_unique[anggota_komisi.user_id,komisi_id,' . $komisi_id . ']',
                'errors' => [
                    'required' => 'Anggota harus dipilih',
                    'numeric' => 'Data anggota tidak valid',
                    'is_unique' => 'Anggota sudah terdaftar dalam komisi ini'
                ]
            ],
            'jabatan' => [
                'rules' => 'required|in_list[anggota,koordinator,staff]',
                'errors' => [
                    'required' => 'Jabatan harus dipilih',
                    'in_list' => 'Jabatan tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'komisi_id' => $komisi_id,
            'user_id' => $this->request->getVar('user_id'),
            'jabatan' => $this->request->getVar('jabatan'),
            'tanggal_bergabung' => $this->request->getVar('tanggal_bergabung') ?: date('Y-m-d'),
            'keterangan' => $this->request->getVar('keterangan'),
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('user_id')
        ];

        if ($this->anggotaKomisiModel->insert($data)) {
            session()->setFlashdata('success', 'Anggota berhasil ditambahkan');
            return redirect()->to('/komisi/show/' . $komisi_id);
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan anggota');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menghapus anggota komisi
     */
    public function hapusAnggota($komisi_id, $anggota_id)
    {
        if ($this->anggotaKomisiModel->delete($anggota_id)) {
            session()->setFlashdata('success', 'Anggota berhasil dihapus');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus anggota');
        }

        return redirect()->to('/komisi/show/' . $komisi_id);
    }

    /**
     * Dashboard komisi
     */
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard Komisi',
            'totalKomisi' => $this->komisiModel->countAll(),
            'komisiAktif' => $this->komisiModel->where('status', 'aktif')->countAllResults(),
            'komisiNonAktif' => $this->komisiModel->where('status !=', 'aktif')->countAllResults(),
            'totalAnggota' => $this->anggotaKomisiModel->countAll(),
            'programTahunIni' => $this->programKerjaModel->getProgramTahunIni(),
            'komisiTerbaru' => $this->komisiModel->orderBy('created_at', 'DESC')->limit(5)->findAll(),
            'activeMenu' => 'dashboard'
        ];

        return view('komisi/dashboard', $data);
    }

    /**
     * API: Mendapatkan data komisi
     */
    public function apiKomisi()
    {
        $komisi = $this->komisiModel->getAllKomisiWithDetails();

        return $this->respond([
            'success' => true,
            'data' => $komisi,
            'total' => count($komisi)
        ]);
    }

    /**
     * API: Mendapatkan anggota komisi
     */
    public function apiAnggota($komisi_id)
    {
        $anggota = $this->anggotaKomisiModel->getAnggotaByKomisi($komisi_id);

        return $this->respond([
            'success' => true,
            'data' => $anggota,
            'total' => count($anggota)
        ]);
    }

    /**
     * Helper: Menambahkan pengurus komisi
     */
    private function addPengurus($komisi_id, $jabatan, $user_id)
    {
        if ($user_id) {
            $data = [
                'komisi_id' => $komisi_id,
                'user_id' => $user_id,
                'jabatan' => $jabatan,
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Cek apakah sudah ada
            $existing = $this->anggotaKomisiModel
                ->where('komisi_id', $komisi_id)
                ->where('user_id', $user_id)
                ->first();
            
            if (!$existing) {
                $this->anggotaKomisiModel->insert($data);
            } else {
                // Update jabatan jika sudah ada
                $this->anggotaKomisiModel
                    ->where('komisi_id', $komisi_id)
                    ->where('user_id', $user_id)
                    ->set('jabatan', $jabatan)
                    ->update();
            }
        }
    }

    /**
     * Helper: Update pengurus komisi
     */
    private function updatePengurus($komisi_id, $jabatan, $user_id)
    {
        // Hapus pengurus lama untuk jabatan ini
        $this->anggotaKomisiModel
            ->where('komisi_id', $komisi_id)
            ->where('jabatan', $jabatan)
            ->delete();

        // Tambah pengurus baru jika ada
        if ($user_id) {
            $this->addPengurus($komisi_id, $jabatan, $user_id);
        }
    }

    /**
     * Helper: Mendapatkan opsi jabatan
     */
    private function getJabatanOptions()
    {
        return [
            'anggota' => 'Anggota',
            'koordinator' => 'Koordinator',
            'staff' => 'Staff',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'wakil_ketua' => 'Wakil Ketua',
            'ketua' => 'Ketua'
        ];
    }
}
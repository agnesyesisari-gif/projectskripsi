<?php

namespace App\Controllers;

use App\Models\JadwalPetugasIbadahModel;
use App\Models\JadwalIbadahModel;
use App\Models\JemaatModel;

class JadwalPetugasIbadah extends BaseController
{
    protected $petugasModel;
    protected $ibadahModel;
    protected $jemaatModel;
    
    public function __construct()
    {
        $this->petugasModel = new JadwalPetugasIbadahModel();
        $this->ibadahModel = new JadwalIbadahModel();
        $this->jemaatModel = new JemaatModel();
        
        helper(['form', 'text']);
    }
    
    public function index()
    {
        $peran = $this->request->getGet('peran');
        $status = $this->request->getGet('status');
        $id_ibadah = $this->request->getGet('id_ibadah');
        
        $data = [
            'title' => 'Jadwal Petugas Ibadah',
            'petugas' => $this->petugasModel->getAllPetugasWithDetails($id_ibadah, $status, $peran),
            'ibadahList' => $this->ibadahModel->findAll(),
            'peranOptions' => $this->petugasModel->getPeranOptions(),
            'statusOptions' => $this->petugasModel->getStatusOptions(),
            'selectedPeran' => $peran,
            'selectedStatus' => $status,
            'selectedIbadah' => $id_ibadah
        ];
        
        return view('jadwal_petugas_ibadah/index', $data);
    }
    
    public function create()
    {
        $id_ibadah = $this->request->getGet('id_ibadah');
        
        $data = [
            'title' => 'Tambah Petugas Ibadah',
            'ibadah' => $id_ibadah ? $this->ibadahModel->find($id_ibadah) : null,
            'ibadahList' => $this->ibadahModel->findAll(),
            'jemaatList' => $this->jemaatModel->where('status_jemaat', 'aktif')->findAll(),
            'peranOptions' => $this->petugasModel->getPeranOptions(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('jadwal_petugas_ibadah/create', $data);
    }
    
    public function store()
    {
        // Validation
        if (!$this->validate($this->petugasModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $id_ibadah = $this->request->getPost('id_ibadah');
        $id_jemaat = $this->request->getPost('id_jemaat');
        $peran = $this->request->getPost('peran');
        
        // Check duplicate assignment
        if ($this->petugasModel->checkJemaatAssignment($id_ibadah, $id_jemaat)) {
            return redirect()->back()->withInput()->with('error', 'Jemaat ini sudah ditugaskan dalam ibadah ini!');
        }
        
        // Check peran availability
        $peranOptions = $this->petugasModel->getPeranOptions();
        $max_per_role = $peranOptions[$peran]['max_per_service'] ?? null;
        
        if (!$this->petugasModel->checkPeranAvailability($id_ibadah, $peran, $max_per_role)) {
            return redirect()->back()->withInput()->with('error', 'Kuota untuk peran ini sudah penuh!');
        }
        
        // Save data
        $data = [
            'id_ibadah' => $id_ibadah,
            'id_jemaat' => $id_jemaat,
            'peran' => $peran,
            'keterangan' => $this->request->getPost('keterangan'),
            'status_konfirmasi' => 'menunggu'
        ];
        
        if ($this->petugasModel->insert($data)) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('success', 'Petugas berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan petugas');
        }
    }
    
    public function edit($id)
    {
        $petugas = $this->petugasModel->getPetugasWithDetails($id);
        
        if (!$petugas) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Data tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Petugas Ibadah',
            'petugas' => $petugas,
            'ibadahList' => $this->ibadahModel->findAll(),
            'jemaatList' => $this->jemaatModel->where('status_jemaat', 'aktif')->findAll(),
            'peranOptions' => $this->petugasModel->getPeranOptions(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('jadwal_petugas_ibadah/edit', $data);
    }
    
    public function update($id)
    {
        // Check if exists
        $petugas = $this->petugasModel->find($id);
        if (!$petugas) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Data tidak ditemukan');
        }
        
        // Validation
        if (!$this->validate($this->petugasModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $id_ibadah = $this->request->getPost('id_ibadah');
        $id_jemaat = $this->request->getPost('id_jemaat');
        $peran = $this->request->getPost('peran');
        
        // Check duplicate assignment (exclude current)
        if ($this->petugasModel->checkJemaatAssignment($id_ibadah, $id_jemaat, $id)) {
            return redirect()->back()->withInput()->with('error', 'Jemaat ini sudah ditugaskan dalam ibadah ini!');
        }
        
        // Check peran availability (exclude current)
        $peranOptions = $this->petugasModel->getPeranOptions();
        $max_per_role = $peranOptions[$peran]['max_per_service'] ?? null;
        
        if (!$this->petugasModel->checkPeranAvailability($id_ibadah, $peran, $max_per_role, $id)) {
            return redirect()->back()->withInput()->with('error', 'Kuota untuk peran ini sudah penuh!');
        }
        
        // Update data
        $data = [
            'id_jadwal_petugas' => $id,
            'id_ibadah' => $id_ibadah,
            'id_jemaat' => $id_jemaat,
            'peran' => $peran,
            'keterangan' => $this->request->getPost('keterangan'),
            'status_konfirmasi' => $this->request->getPost('status_konfirmasi')
        ];
        
        if ($this->petugasModel->save($data)) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('success', 'Petugas berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui petugas');
        }
    }
    
    public function delete($id)
    {
        if ($this->petugasModel->delete($id)) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('success', 'Petugas berhasil dihapus');
        } else {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Gagal menghapus petugas');
        }
    }
    
    public function detail($id)
    {
        $petugas = $this->petugasModel->getPetugasWithDetails($id);
        
        if (!$petugas) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Data tidak ditemukan');
        }
        
        $data = [
            'title' => 'Detail Petugas Ibadah',
            'petugas' => $petugas,
            'peranOptions' => $this->petugasModel->getPeranOptions(),
            'statusOptions' => $this->petugasModel->getStatusOptions()
        ];
        
        return view('jadwal_petugas_ibadah/detail', $data);
    }
    
    public function konfirmasi($id)
    {
        $status = $this->request->getPost('status');
        $keterangan = $this->request->getPost('keterangan');
        
        if ($this->petugasModel->updateKonfirmasi($id, $status, $keterangan)) {
            return redirect()->back()->with('success', 'Status konfirmasi berhasil diperbarui');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui status konfirmasi');
        }
    }
    
    public function byIbadah($id_ibadah)
    {
        $ibadah = $this->ibadahModel->find($id_ibadah);
        
        if (!$ibadah) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Ibadah tidak ditemukan');
        }
        
        $data = [
            'title' => 'Petugas Ibadah: ' . $ibadah['judul_ibadah'],
            'petugas' => $this->petugasModel->getAllPetugasWithDetails($id_ibadah),
            'ibadah' => $ibadah,
            'peranOptions' => $this->petugasModel->getPeranOptions()
        ];
        
        return view('jadwal_petugas_ibadah/by_ibadah', $data);
    }
  
    public function generateAuto($id_ibadah)
    {
        $ibadah = $this->ibadahModel->find($id_ibadah);
        
        if (!$ibadah) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Ibadah tidak ditemukan');
        }
        
        // Configurasi peran yang dibutuhkan
        $peran_config = [
            'pemimpin_ibadah' => ['jumlah' => 1],
            'pemandu_pujian' => ['jumlah' => 2],
            'pemusik' => ['jumlah' => 4],
            'penatua' => ['jumlah' => 2],
            'diaken' => ['jumlah' => 2]
        ];
        
        // Exclude jemaat yang sudah ditugaskan
        $existing_petugas = $this->petugasModel->getPetugasByIbadah($id_ibadah);
        $exclude_jemaat = array_column($existing_petugas, 'id_jemaat');
        
        foreach ($peran_config as &$config) {
            $config['exclude'] = $exclude_jemaat;
        }
        
        $result = $this->petugasModel->generateAutoSchedule($id_ibadah, $peran_config);
        
        $message = 'Generate otomatis selesai. ';
        $message .= count($result['success']) . ' petugas berhasil ditambahkan. ';
        $message .= count($result['failed']) . ' gagal.';
        
        return redirect()->to('/jadwal-petugas-ibadah/by-ibadah/' . $id_ibadah)
                        ->with('success', $message)
                        ->with('generate_result', $result);
    }
    
    public function laporan()
    {
        $start_date = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end_date = $this->request->getGet('end_date') ?? date('Y-m-t');
        $peran = $this->request->getGet('peran');
        
        $data = [
            'title' => 'Laporan Petugas Ibadah',
            'petugas' => $peran ? $this->petugasModel->getPetugasByPeranDateRange($peran, $start_date, $end_date) : [],
            'peranOptions' => $this->petugasModel->getPeranOptions(),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'selectedPeran' => $peran
        ];
        
        return view('jadwal_petugas_ibadah/laporan', $data);
    }
    
    public function exportPDF($id_ibadah)
    {
        $ibadah = $this->ibadahModel->find($id_ibadah);
        $petugas = $this->petugasModel->getAllPetugasWithDetails($id_ibadah, 'dikonfirmasi');
        
        if (!$ibadah) {
            return redirect()->to('/jadwal-petugas-ibadah')->with('error', 'Ibadah tidak ditemukan');
        }
        
        // Group petugas by peran
        $groupedPetugas = [];
        foreach ($petugas as $p) {
            $groupedPetugas[$p['peran']][] = $p;
        }
        
        $data = [
            'ibadah' => $ibadah,
            'groupedPetugas' => $groupedPetugas,
            'peranOptions' => $this->petugasModel->getPeranOptions()
        ];
        
        // Return PDF view (you need to implement PDF library like Dompdf)
        return view('jadwal_petugas_ibadah/export_pdf', $data);
    }
    
    public function mySchedule()
    {
        // Assuming current user/jemaat ID is stored in session
        $id_jemaat = session()->get('id_jemaat'); // Adjust according to your auth system
        
        if (!$id_jemaat) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }
        
        $status = $this->request->getGet('status');
        
        $data = [
            'title' => 'Jadwal Tugas Saya',
            'jadwal' => $this->petugasModel->getJadwalForJemaat($id_jemaat, $status),
            'statusOptions' => $this->petugasModel->getStatusOptions(),
            'selectedStatus' => $status,
            'jemaat' => $this->jemaatModel->find($id_jemaat)
        ];
        
        return view('jadwal_petugas_ibadah/my_schedule', $data);
    }
    
    public function respondAssignment($id, $response)
    {
        $petugas = $this->petugasModel->find($id);
        
        if (!$petugas) {
            return redirect()->to('/jadwal-tugas-saya')->with('error', 'Tugas tidak ditemukan');
        }
        
        // Check if jemaat is the assigned one
        $id_jemaat = session()->get('id_jemaat');
        if ($petugas['id_jemaat'] != $id_jemaat) {
            return redirect()->to('/jadwal-tugas-saya')->with('error', 'Akses ditolak');
        }
        
        $status = ($response == 'accept') ? 'dikonfirmasi' : 'ditolak';
        
        if ($this->petugasModel->updateKonfirmasi($id, $status)) {
            $message = ($response == 'accept') ? 'Tugas diterima' : 'Tugas ditolak';
            return redirect()->to('/jadwal-tugas-saya')->with('success', $message);
        } else {
            return redirect()->to('/jadwal-tugas-saya')->with('error', 'Gagal merespons tugas');
        }
    }
}
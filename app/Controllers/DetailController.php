<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JadwalIbadahModel;
use App\Models\TukarMimbarModel;
use App\Models\KegiatanPelayananModel;

class DetailController extends BaseController
{
    protected $jadwalModel;
    protected $tukarMimbarModel;
    protected $kegiatanModel;
    
    public function __construct()
    {
        $this->jadwalModel = new JadwalIbadahModel();
        $this->tukarMimbarModel = new TukarMimbarModel();
        $this->kegiatanModel = new KegiatanPelayananModel();
    }
    
    public function index($jenis = null, $id = null)
    {
        // Authentication check
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }
        
        // Authorization check - get user role
        $userRole = session()->get('role');
        $can_edit = in_array($userRole, ['admin', 'pastor', 'sekretaris']);
        $can_delete = in_array($userRole, ['admin', 'pastor']);
        
        $dataView = [];
        $data = null;
        
        switch ($jenis) {
            case 'jadwal-ibadah':
                $data = $this->jadwalModel->find($id);
                $dataView = [
                    'jenis_data' => 'Jadwal Ibadah Minggu',
                    'jenis_url' => 'jadwal-ibadah',
                    'breadcrumb' => 'Jadwal Ibadah',
                    'kembali_url' => 'jadwal-ibadah',
                    'edit_url' => 'jadwal-ibadah/edit',
                    'icon' => 'fas fa-calendar-alt',
                    'can_edit' => $can_edit,
                    'can_delete' => $can_delete,
                    'data' => $data
                ];
                break;
                
            case 'tukar-mimbar':
                $data = $this->tukarMimbarModel->find($id);
                $dataView = [
                    'jenis_data' => 'Jadwal Tukar Mimbar',
                    'jenis_url' => 'tukar-mimbar',
                    'breadcrumb' => 'Tukar Mimbar',
                    'kembali_url' => 'tukar-mimbar',
                    'edit_url' => 'tukar-mimbar/edit',
                    'icon' => 'fas fa-exchange-alt',
                    'can_edit' => $can_edit,
                    'can_delete' => $can_delete,
                    'data' => $data
                ];
                break;
                
            case 'kegiatan-pelayanan':
                $data = $this->kegiatanModel->find($id);
                $dataView = [
                    'jenis_data' => 'Kegiatan Pelayanan',
                    'jenis_url' => 'kegiatan-pelayanan',
                    'breadcrumb' => 'Kegiatan Pelayanan',
                    'kembali_url' => 'kegiatan-pelayanan',
                    'edit_url' => 'kegiatan-pelayanan/edit',
                    'icon' => 'fas fa-hands-helping',
                    'can_edit' => $can_edit,
                    'can_delete' => $can_delete,
                    'data' => $data
                ];
                break;
                
            default:
                return redirect()->back()->with('error', 'Jenis data tidak valid!');
        }
        
        // Check if data exists
        if (!$data) {
            return redirect()->to($dataView['kembali_url'])->with('error', 'Data tidak ditemukan!');
        }
        
        return view('detail', $dataView);
    }
    
    /**
     * Get detail data via AJAX
     */
    public function getDetail($jenis = null, $id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'status' => 'error',
                'message' => 'Method not allowed'
            ]);
        }
        
        $data = null;
        
        switch ($jenis) {
            case 'jadwal-ibadah':
                $data = $this->jadwalModel->find($id);
                break;
            case 'tukar-mimbar':
                $data = $this->tukarMimbarModel->find($id);
                break;
            case 'kegiatan-pelayanan':
                $data = $this->kegiatanModel->find($id);
                break;
        }
        
        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
        
        // Format date fields
        if (isset($data['tanggal'])) {
            $data['tanggal_formatted'] = date('d F Y', strtotime($data['tanggal']));
        }
        
        if (isset($data['created_at'])) {
            $data['created_at_formatted'] = date('d M Y H:i', strtotime($data['created_at']));
        }
        
        if (isset($data['updated_at'])) {
            $data['updated_at_formatted'] = date('d M Y H:i', strtotime($data['updated_at']));
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
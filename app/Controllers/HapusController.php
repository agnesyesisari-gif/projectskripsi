<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JadwalIbadahModel;
use App\Models\TukarMimbarModel;
use App\Models\KegiatanPelayananModel;

class HapusController extends BaseController
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

    /**
     * Menampilkan form konfirmasi hapus
     */
    public function index($jenis = null, $id = null)
    {
        // Cek jenis data yang akan dihapus
        $dataView = [];
        
        switch ($jenis) {
            case 'jadwal-ibadah':
                $data = $this->jadwalModel->find($id);
                $dataView = [
                    'jenis_data' => 'Jadwal Ibadah Minggu',
                    'data' => $data,
                    'id' => $id,
                    'aksi_hapus' => 'hapus/proses/jadwal-ibadah',
                    'url_kembali' => 'jadwal-ibadah'
                ];
                break;
                
            case 'tukar-mimbar':
                $data = $this->tukarMimbarModel->find($id);
                $dataView = [
                    'jenis_data' => 'Jadwal Tukar Mimbar Klasis',
                    'data' => $data,
                    'id' => $id,
                    'aksi_hapus' => 'hapus/proses/tukar-mimbar',
                    'url_kembali' => 'tukar-mimbar'
                ];
                break;
                
            case 'kegiatan-pelayanan':
                $data = $this->kegiatanModel->find($id);
                $dataView = [
                    'jenis_data' => 'Kegiatan Pelayanan',
                    'data' => $data,
                    'id' => $id,
                    'aksi_hapus' => 'hapus/proses/kegiatan-pelayanan',
                    'url_kembali' => 'kegiatan-pelayanan'
                ];
                break;
                
            default:
                return redirect()->back()->with('error', 'Jenis data tidak valid!');
        }
        
        // Cek apakah data ditemukan
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!');
        }
        
        return view('hapus', $dataView);
    }

    /**
     * Proses penghapusan data
     */
    public function prosesHapus($jenis = null)
    {
        if (!$this->request->is('delete')) {
            return redirect()->back()->with('error', 'Metode tidak diizinkan!');
        }
        
        $id = $this->request->getPost('id');
        $alasan_hapus = $this->request->getPost('alasan_hapus');
        
        switch ($jenis) {
            case 'jadwal-ibadah':
                $model = $this->jadwalModel;
                $redirect_url = 'jadwal-ibadah';
                $success_msg = 'Jadwal ibadah berhasil dihapus!';
                break;
                
            case 'tukar-mimbar':
                $model = $this->tukarMimbarModel;
                $redirect_url = 'tukar-mimbar';
                $success_msg = 'Jadwal tukar mimbar berhasil dihapus!';
                break;
                
            case 'kegiatan-pelayanan':
                $model = $this->kegiatanModel;
                $redirect_url = 'kegiatan-pelayanan';
                $success_msg = 'Kegiatan pelayanan berhasil dihapus!';
                break;
                
            default:
                return redirect()->back()->with('error', 'Jenis data tidak valid!');
        }
        
        try {
            // Cek apakah data ada
            $data = $model->find($id);
            if (!$data) {
                return redirect()->to($redirect_url)->with('error', 'Data tidak ditemukan!');
            }
            
            // Simpan log penghapusan (opsional)
            $this->simpanLogHapus($jenis, $id, $data, $alasan_hapus);
            
            // Hapus data
            if ($model->delete($id)) {
                return redirect()->to($redirect_url)->with('success', $success_msg);
            } else {
                return redirect()->to($redirect_url)->with('error', 'Gagal menghapus data!');
            }
            
        } catch (\Exception $e) {
            return redirect()->to($redirect_url)->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Simpan log penghapusan (opsional)
     */
    private function simpanLogHapus($jenis, $id, $data, $alasan)
    {
        $logModel = new \App\Models\LogAktivitasModel();
        
        $logData = [
            'user_id' => session()->get('user_id'),
            'aktivitas' => 'Hapus ' . $jenis,
            'deskripsi' => 'Menghapus data ' . $jenis . ' ID: ' . $id . 
                         ($alasan ? ' | Alasan: ' . $alasan : ''),
            'data_sebelum' => json_encode($data),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ];
        
        $logModel->insert($logData);
    }
}
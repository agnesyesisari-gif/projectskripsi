<?php

namespace App\Controllers;

use App\Models\KehadiranModel;
use App\Models\KegiatanModel;
use App\Models\JemaatModel;

class Kehadiran extends BaseController
{
    protected $kehadiranModel;
    protected $kegiatanModel;
    protected $jemaatModel;

    public function __construct()
    {
        $this->kehadiranModel = new KehadiranModel();
        $this->kegiatanModel = new KegiatanModel();
        $this->jemaatModel = new JemaatModel();
        
        // Proteksi halaman
        helper('auth');
        if (!logged_in()) {
            return redirect()->to('/login');
        }
    }

    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        $perPage = 20;
        $currentPage = $this->request->getVar('page_kehadiran') ? $this->request->getVar('page_kehadiran') : 1;
        
        $data = [
            'title' => 'Data Kehadiran Jemaat',
            'kehadiran' => $this->kehadiranModel->getKehadiranWithDetails($perPage, $currentPage),
            'pager' => $this->kehadiranModel->pager,
            'kegiatan' => $this->kegiatanModel->findAll(),
            'jemaat' => $this->jemaatModel->where('status_aktif', 1)->findAll(),
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'validation' => \Config\Services::validation()
        ];

        return view('kehadiran/index', $data);
    }

    /**
     * Rekap kehadiran per kegiatan
     */
    public function rekap($kegiatan_id)
    {
        $kegiatan = $this->kegiatanModel->find($kegiatan_id);
        if (!$kegiatan) {
            return redirect()->to('/kehadiran')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $kehadiran = $this->kehadiranModel->getKehadiranByKegiatan($kegiatan_id);
        $totalJemaat = $this->jemaatModel->where('status_aktif', 1)->countAllResults();
        $totalHadir = count($kehadiran);

        $data = [
            'title' => 'Rekap Kehadiran',
            'kegiatan' => $kegiatan,
            'kehadiran' => $kehadiran,
            'total_jemaat' => $totalJemaat,
            'total_hadir' => $totalHadir,
            'persentase' => $totalJemaat > 0 ? round(($totalHadir / $totalJemaat) * 100, 2) : 0
        ];

        return view('kehadiran/rekap', $data);
    }

    /**
     * Export rekap kehadiran ke PDF
     */
    public function exportPDF($kegiatan_id)
    {
        $kegiatan = $this->kegiatanModel->find($kegiatan_id);
        if (!$kegiatan) {
            return redirect()->to('/kehadiran')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $kehadiran = $this->kehadiranModel->getKehadiranByKegiatan($kegiatan_id);
        $totalJemaat = $this->jemaatModel->where('status_aktif', 1)->countAllResults();
        $totalHadir = count($kehadiran);

        $data = [
            'title' => 'Rekap Kehadiran - ' . $kegiatan->nama_kegiatan,
            'kegiatan' => $kegiatan,
            'kehadiran' => $kehadiran,
            'total_jemaat' => $totalJemaat,
            'total_hadir' => $totalHadir,
            'persentase' => $totalJemaat > 0 ? round(($totalHadir / $totalJemaat) * 100, 2) : 0,
            'export_date' => date('d/m/Y H:i:s')
        ];

        $html = view('kehadiran/export/rekap_pdf', $data);
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('rekap_kehadiran_' . $kegiatan_id . '.pdf', ['Attachment' => true]);
    }

    /**
     * Hapus kehadiran tertentu
     */
    public function delete($id)
    {
        $kehadiran = $this->kehadiranModel->find($id);
        
        if (!$kehadiran) {
            return redirect()->back()->with('error', 'Data kehadiran tidak ditemukan.');
        }

        $kegiatan_id = $kehadiran['kegiatan_id'];
        $this->kehadiranModel->delete($id);
        
        return redirect()->to('/kehadiran/rekap/' . $kegiatan_id)->with('success', 'Data kehadiran berhasil dihapus.');
    }
}
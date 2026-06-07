<?php

namespace App\Controllers;

use App\Models\KegiatanModel;
use App\Models\JenisKegiatanModel;

class Kegiatan extends BaseController
{
    protected $kegiatanModel;
    protected $jenisKegiatanModel;

    public function __construct()
    {
        $this->kegiatanModel = new KegiatanModel();
        $this->jenisKegiatanModel = new JenisKegiatanModel();
        
        // Proteksi halaman
        helper('auth');
        if (!logged_in()) {
            return redirect()->to('/login');
        }
    }

    /**
     * Menampilkan daftar kegiatan
     */
    public function index()
    {
        $data = [
            'title' => 'Daftar Kegiatan Pelayanan Gereja',
            'kegiatan' => $this->kegiatanModel->getKegiatanWithJenis(),
            'jenis_kegiatan' => $this->jenisKegiatanModel->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('kegiatan/index', $data);
    }

    /**
     * Menampilkan jadwal ibadah
     */
    public function jadwalIbadah()
    {
        $data = [
            'title' => 'Jadwal Ibadah',
            'jadwal' => $this->kegiatanModel->where('jenis_kegiatan_id', 1) // 1 = id untuk jadwal ibadah
                                           ->orderBy('tanggal', 'ASC')
                                           ->orderBy('waktu_mulai', 'ASC')
                                           ->findAll(),
            'jenis' => 'Ibadah'
        ];

        return view('kegiatan/jadwal', $data);
    }

    /**
     * Menampilkan program kerja
     */
    public function programKerja()
    {
        $data = [
            'title' => 'Program Kerja Pelayanan',
            'program' => $this->kegiatanModel->where('jenis_kegiatan_id', 2) // 2 = id untuk program kerja
                                            ->orderBy('tanggal_mulai', 'ASC')
                                            ->findAll(),
            'jenis' => 'Program Kerja'
        ];

        return view('kegiatan/program', $data);
    }

    /**
     * Menampilkan kalender kegiatan
     */
    public function kalender()
    {
        // Ambil data untuk fullcalendar
        $events = $this->kegiatanModel->findAll();
        $eventArray = [];

        foreach ($events as $event) {
            $color = $event->jenis_kegiatan_id == 1 ? '#3498db' : '#2ecc71'; // Warna berbeda untuk jenis kegiatan
            
            $eventArray[] = [
                'title' => $event->nama_kegiatan,
                'start' => $event->tanggal . 'T' . $event->waktu_mulai,
                'end' => $event->tanggal . 'T' . $event->waktu_selesai,
                'color' => $color,
                'extendedProps' => [
                    'lokasi' => $event->lokasi,
                    'penanggung_jawab' => $event->penanggung_jawab,
                    'keterangan' => $event->keterangan
                ]
            ];
        }

        $data = [
            'title' => 'Kalender Kegiatan Gereja',
            'events' => json_encode($eventArray)
        ];

        return view('kegiatan/kalender', $data);
    }

    /**
     * Menyimpan data kegiatan baru
     */
    public function save()
    {
        // Validasi input
        $rules = [
            'nama_kegiatan' => 'required|max_length[255]',
            'jenis_kegiatan_id' => 'required',
            'tanggal' => 'required|valid_date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'tempat' => 'required|max_length[255]',
            'penanggung_jawab' => 'required|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_kegiatan' => $this->request->getVar('nama_kegiatan'),
            'jenis_kegiatan_id' => $this->request->getVar('jenis_kegiatan_id'),
            'tanggal' => $this->request->getVar('tanggal'),
            'waktu_mulai' => $this->request->getVar('waktu_mulai'),
            'waktu_selesai' => $this->request->getVar('waktu_selesai'),
            'tempat' => $this->request->getVar('tempat'),
            'penanggung_jawab' => $this->request->getVar('penanggung_jawab'),
            'keterangan' => $this->request->getVar('keterangan'),
            'created_by' => user_id()
        ];

        $this->kegiatanModel->save($data);
        
        return redirect()->to('/kegiatan')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    /**
     * Mengupdate data kegiatan
     */
    public function update($id)
    {
        // Cek apakah kegiatan exist
        $kegiatan = $this->kegiatanModel->find($id);
        if (!$kegiatan) {
            return redirect()->to('/kegiatan')->with('error', 'Kegiatan tidak ditemukan.');
        }

        // Validasi input
        $rules = [
            'nama_kegiatan' => 'required|max_length[255]',
            'jenis_kegiatan_id' => 'required',
            'tanggal' => 'required|valid_date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'tempat' => 'required|max_length[255]',
            'penanggung_jawab' => 'required|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'nama_kegiatan' => $this->request->getVar('nama_kegiatan'),
            'jenis_kegiatan_id' => $this->request->getVar('jenis_kegiatan_id'),
            'tanggal' => $this->request->getVar('tanggal'),
            'waktu_mulai' => $this->request->getVar('waktu_mulai'),
            'waktu_selesai' => $this->request->getVar('waktu_selesai'),
            'tempat' => $this->request->getVar('lokasi'),
            'penanggung_jawab' => $this->request->getVar('penanggung_jawab'),
            'keterangan' => $this->request->getVar('keterangan'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => user_id()
        ];

        $this->kegiatanModel->save($data);
        
        return redirect()->to('/kegiatan')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Menghapus data kegiatan
     */
    public function delete($id)
    {
        $kegiatan = $this->kegiatanModel->find($id);
        
        if (!$kegiatan) {
            return redirect()->to('/kegiatan')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $this->kegiatanModel->delete($id);
        
        return redirect()->to('/kegiatan')->with('success', 'Kegiatan berhasil dihapus.');
    }

    /**
     * Mendapatkan data kegiatan untuk API (JSON)
     */
    public function apiKegiatan()
    {
        $start = $this->request->getGet('start');
        $end = $this->request->getGet('end');
        
        $query = $this->kegiatanModel;
        
        if ($start && $end) {
            $query->where('tanggal >=', $start)
                  ->where('tanggal <=', $end);
        }
        
        $kegiatan = $query->orderBy('tanggal', 'ASC')
                         ->orderBy('waktu_mulai', 'ASC')
                         ->findAll();
        
        return $this->response->setJSON($kegiatan);
    }

    /**
     * Menampilkan detail kegiatan
     */
    public function detail($id)
    {
        $kegiatan = $this->kegiatanModel->getKegiatanWithJenis($id);
        
        if (!$kegiatan) {
            return redirect()->to('/kegiatan')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Kegiatan',
            'kegiatan' => $kegiatan
        ];

        return view('kegiatan/detail', $data);
    }

    /**
     * Export jadwal ke PDF
     */
    public function exportPDF($type = 'jadwal')
    {
        $dompdf = new \Dompdf\Dompdf();
        
        if ($type == 'jadwal') {
            $data['kegiatan'] = $this->kegiatanModel->where('jenis_kegiatan_id', 1)
                                                   ->orderBy('tanggal', 'ASC')
                                                   ->orderBy('waktu_mulai', 'ASC')
                                                   ->findAll();
            $data['title'] = 'Jadwal Ibadah';
            $html = view('kegiatan/export/jadwal_pdf', $data);
        } else {
            $data['kegiatan'] = $this->kegiatanModel->where('jenis_kegiatan_id', 2)
                                                   ->orderBy('tanggal_mulai', 'ASC')
                                                   ->findAll();
            $data['title'] = 'Program Kerja';
            $html = view('kegiatan/export/program_pdf', $data);
        }

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($data['title'] . '.pdf', ['Attachment' => true]);
    }
}
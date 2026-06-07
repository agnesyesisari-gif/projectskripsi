<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AktivitasJemaatModel;
use App\Models\AnggotaModel;
use App\Models\IbadahModel;
use App\Models\KegiatanModel;
use App\Models\PelayananModel;
use App\Models\WilayahModel;
use CodeIgniter\API\ResponseTrait;

class AktivitasJemaatController extends BaseController
{
    use ResponseTrait;

    protected $aktivitasModel;
    protected $anggotaModel;
    protected $ibadahModel;
    protected $kegiatanModel;
    protected $pelayananModel;
    protected $wilayahModel;

    public function __construct()
    {
        $this->aktivitasModel = new AktivitasJemaatModel();
        $this->anggotaModel = new AnggotaModel();
        $this->ibadahModel = new IbadahModel();
        $this->kegiatanModel = new KegiatanModel();
        $this->pelayananModel = new PelayananModel();
        $this->wilayahModel = new WilayahModel();
        
        helper(['form', 'url', 'date', 'text']);
    }

    /**
     * Dashboard Aktivitas Jemaat
     */
    public function index()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        $wilayahId = $this->request->getGet('wilayah');

        $data = [
            'title' => 'Dashboard Aktivitas Jemaat',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat']
            ],
            'totalHadir' => $this->aktivitasModel->getTotalKehadiranPeriode($startDate, $endDate, $wilayahId),
            'topAktif' => $this->aktivitasModel->getJemaatPalingAktif($startDate, $endDate, 10, $wilayahId),
            'kegiatan' => $this->aktivitasModel->getKegiatan($startDate, $endDate, 5),
            'recentActivities' => $this->aktivitasModel->getAktivitasTerbaru(15),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'wilayahId' => $wilayahId,
            'listWilayah' => $this->wilayahModel->findAll()
        ];

        return view('admin/aktivitas/index', $data);
    }

    /**
     * Daftar Kehadiran Ibadah
     */
    public function kehadiranIbadah()
    {
        $ibadahId = $this->request->getGet('ibadah_id');
        $tanggal = $this->request->getGet('tanggal');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 30;

        // Jika ada ibadah_id spesifik
        if ($ibadahId) {
            $ibadah = $this->ibadahModel->find($ibadahId);
            $kehadiran = $this->aktivitasModel->getKehadiranIbadah($ibadahId, $perPage, $page);
            $pager = $this->aktivitasModel->pager;
            
            $title = "Kehadiran Ibadah: " . ($ibadah['nama_ibadah'] ?? '');
        } else {
            // Tampilkan semua kehadiran dengan filter tanggal
            $kehadiran = $this->aktivitasModel->getAllKehadiranIbadah($tanggal, $perPage, $page);
            $pager = $this->aktivitasModel->pager;
            
            $title = "Daftar Kehadiran Ibadah" . ($tanggal ? " Tanggal $tanggal" : '');
        }

        $data = [
            'title' => $title,
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat'],
                ['title' => 'Kehadiran Ibadah', 'url' => '/aktivitas-jemaat/kehadiran-ibadah']
            ],
            'kehadiran' => $kehadiran,
            'pager' => $pager,
            'ibadahId' => $ibadahId,
            'tanggal' => $tanggal,
            'listIbadah' => $this->ibadahModel->where('status', 'active')->findAll(),
            'totalHadir' => $this->aktivitasModel->countKehadiranIbadah($ibadahId, $tanggal),
            'totalJemaat' => $this->anggotaModel->where('status_anggota', 'active')->countAllResults()
        ];

        return view('admin/aktivitas/kehadiran_ibadah', $data);
    }

    /**
     * Form Absensi Manual
     */
    public function absensiManual()
    {
        $ibadahId = $this->request->getGet('ibadah_id');
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');

        $data = [
            'title' => 'Absensi Manual',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat'],
                ['title' => 'Absensi Manual', 'url' => '/aktivitas-jemaat/absensi-manual']
            ],
            'ibadahId' => $ibadahId,
            'tanggal' => $tanggal,
            'listIbadah' => $this->ibadahModel->where('status', 'active')->findAll(),
            'jemaat' => $this->anggotaModel->where('status_anggota', 'active')->findAll(),
            'sudahAbsen' => $ibadahId ? $this->aktivitasModel->getJemaatSudahAbsen($ibadahId, $tanggal) : [],
            'validation' => \Config\Services::validation()
        ];

        return view('admin/aktivitas/absensi_manual', $data);
    }

    /**
     * Proses Absensi Manual
     */
    public function prosesAbsensi()
    {
        if (!$this->validate([
            'ibadah_id' => 'required|numeric',
            'tanggal' => 'required|valid_date',
            'jemaat_ids' => 'permit_empty'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ibadahId = $this->request->getPost('ibadah_id');
        $tanggal = $this->request->getPost('tanggal');
        $jemaatIds = $this->request->getPost('jemaat_ids') ? explode(',', $this->request->getPost('jemaat_ids')) : [];
        $keterangan = $this->request->getPost('keterangan');

        $ibadah = $this->ibadahModel->find($ibadahId);
        if (!$ibadah) {
            return redirect()->back()->with('error', 'Ibadah tidak ditemukan.');
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($jemaatIds as $jemaatId) {
            // Cek apakah sudah absen
            $sudahAbsen = $this->aktivitasModel->where([
                'anggota_id' => $jemaatId,
                'ibadah_id' => $ibadahId,
                'DATE(tanggal)' => $tanggal
            ])->first();

            if ($sudahAbsen) {
                $errors[] = "Jemaat ID $jemaatId sudah absen";
                $errorCount++;
                continue;
            }

            // Simpan absensi
            $absensiData = [
                'anggota_id' => $jemaatId,
                'ibadah_id' => $ibadahId,
                'jenis_aktivitas' => 'ibadah',
                'tanggal' => $tanggal,
                'waktu_hadir' => date('H:i:s'),
                'status_kehadiran' => 'hadir',
                'keterangan' => $keterangan,
                'metode_absen' => 'manual',
                'dicatat_oleh' => session()->get('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->aktivitasModel->insert($absensiData)) {
                $successCount++;
                
                // Update kehadiran jemaat
                $this->updateJemaat($jemaatId);
            } else {
                $errorCount++;
                $errors[] = "Gagal menyimpan absensi untuk jemaat ID $jemaatId";
            }
        }

        $message = "Absensi berhasil diproses: $successCount berhasil, $errorCount gagal.";
        if ($errorCount > 0) {
            $message .= " Error: " . implode(', ', $errors);
        }

        return redirect()->to('/aktivitas-jemaat/kehadiran-ibadah?ibadah_id=' . $ibadahId)
                         ->with($errorCount == 0 ? 'success' : 'warning', $message);
    }

    /**
     * Aktivitas Pelayanan Jemaat
     */
    public function aktivitasPelayanan()
    {
        $anggotaId = $this->request->getGet('anggota_id');
        $pelayananId = $this->request->getGet('pelayanan_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 30;

        $data = [
            'title' => 'Aktivitas Pelayanan Jemaat',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat'],
                ['title' => 'Aktivitas Pelayanan', 'url' => '/aktivitas-jemaat/pelayanan']
            ],
            'aktivitas' => $this->aktivitasModel->getAktivitasPelayanan($anggotaId, $pelayananId, $startDate, $endDate, $perPage, $page),
            'pager' => $this->aktivitasModel->pager,
            'anggotaId' => $anggotaId,
            'pelayananId' => $pelayananId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'listAnggota' => $this->anggotaModel->where('status_anggota', 'active')->findAll(),
            'listPelayanan' => $this->pelayananModel->findAll(),
        ];

        return view('admin/aktivitas/pelayanan', $data);
    }

    /**
     * Tambah Aktivitas Pelayanan
     */
    public function tambahPelayanan()
    {
        $data = [
            'title' => 'Tambah Aktivitas Pelayanan',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat'],
                ['title' => 'Aktivitas Pelayanan', 'url' => '/aktivitas-jemaat/pelayanan'],
                ['title' => 'Tambah', 'url' => '/aktivitas-jemaat/tambah-pelayanan']
            ],
            'listAnggota' => $this->anggotaModel->where('status_anggota', 'active')->findAll(),
            'listPelayanan' => $this->pelayananModel->findAll(),
            'listKegiatan' => $this->kegiatanModel->where('status', 'active')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/aktivitas/tambah_pelayanan', $data);
    }

    /**
     * Simpan Aktivitas Pelayanan
     */
    public function simpanPelayanan()
    {
        if (!$this->validate([
            'anggota_id' => 'required|numeric',
            'pelayanan_id' => 'required|numeric',
            'kegiatan_id' => 'permit_empty|numeric',
            'tanggal' => 'required|valid_date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'deskripsi' => 'required|min_length[10]|max_length[500]',
            'tempat' => 'permit_empty|max_length[200]'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aktivitasData = [
            'anggota_id' => $this->request->getPost('anggota_id'),
            'pelayanan_id' => $this->request->getPost('pelayanan_id'),
            'kegiatan_id' => $this->request->getPost('kegiatan_id'),
            'jenis_aktivitas' => '',
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_mulai' => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tempat' => $this->request->getPost('tempat'),
            'status_kehadiran' => '',
            'dicatat_oleh' => session()->get(''),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->aktivitasModel->insert($aktivitasData)) {
            // Update pelayanan jemaat
            $this->updatePelayanan($aktivitasData['anggota_id']);
            
            return redirect()->to('/aktivitas-jemaat/pelayanan')
                             ->with('success', 'Aktivitas pelayanan berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan aktivitas pelayanan.');
    }

    /**
     * Detail Aktivitas Jemaat
     */
    public function detail($anggotaId)
    {
        $anggota = $this->anggotaModel->find($anggotaId);
        if (!$anggota) {
            return redirect()->back()->with('error', 'Data jemaat tidak ditemukan.');
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        $data = [
            'title' => 'Detail Aktivitas: ' . $anggota['nama_lengkap'],
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat'],
                ['title' => 'Detail Aktivitas', 'url' => '/aktivitas-jemaat/detail/' . $anggotaId]
            ],
            'anggota' => $anggota,
            'kehadiranIbadah' => $this->aktivitasModel->getKehadiranIbadahIndividu($anggotaId, $startDate, $endDate, 20),
            'aktivitasPelayanan' => $this->aktivitasModel->getAktivitasPelayananIndividu($anggotaId, $startDate, $endDate, 20),
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        return view('admin/aktivitas/detail', $data);
    }

    /**
     * Laporan Aktivitas
     */
    public function laporan()
    {
        $jenisLaporan = $this->request->getGet('jenis') ?? 'kehadiran';
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        $wilayahId = $this->request->getGet('wilayah');
        $format = $this->request->getGet('format') ?? 'view';

        switch ($jenisLaporan) {
            case 'kehadiran':
                $data = $this->aktivitasModel->getLaporanKehadiran($startDate, $endDate, $wilayahId);
                $title = "Laporan Kehadiran Ibadah";
                $columns = ['Nama', 'Wilayah', 'Total Hadir', 'Persentase'];
                break;
                
            case 'pelayanan':
                $data = $this->aktivitasModel->getLaporanPelayanan($startDate, $endDate, $wilayahId);
                $title = "Laporan Aktivitas Pelayanan";
                $columns = ['Nama', 'Pelayanan', 'Jumlah Kegiatan'];
                break;
                
            default:
                $data = [];
                $title = "Laporan";
                $columns = [];
        }

        if ($format == 'pdf') {
            return $this->generatePDF($data, $title, $columns, $jenisLaporan);
        }

        $viewData = [
            'title' => $title,
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Aktivitas Jemaat', 'url' => '/aktivitas-jemaat'],
                ['title' => 'Laporan', 'url' => '/aktivitas-jemaat/laporan']
            ],
            'laporan' => $data,
            'jenisLaporan' => $jenisLaporan,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'columns' => $columns,
            'periode' => date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate))
        ];

        return view('admin/aktivitas/laporan', $viewData);
    }

    /**
     * Generate PDF
     */
    private function generatePDF($data, $title, $columns, $jenisLaporan)
    {
        // Menggunakan DomPDF
        $dompdf = new \Dompdf\Dompdf();
        
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { text-align: center; color: #333; }
                .periode { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #f2f2f2; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                .footer { margin-top: 30px; text-align: right; font-size: 12px; }
            </style>
        </head>
        <body>
            <h1>' . $title . '</h1>
            <div class="periode">Periode: ' . date('d/m/Y', strtotime($this->request->getGet('start_date'))) . 
                      ' - ' . date('d/m/Y', strtotime($this->request->getGet('end_date'))) . '</div>
            <table>
                <thead>
                    <tr>';
        
        foreach ($columns as $column) {
            $html .= '<th>' . $column . '</th>';
        }
        
        $html .= '</tr></thead><tbody>';
        
        foreach ($data as $item) {
            $html .= '<tr>';
            
            switch ($jenisLaporan) {
                case 'kehadiran':
                    $html .= '<td>' . $item['nama'] . '</td>';
                    $html .= '<td>' . $item['total_hadir'] . '</td>';
                    $html .= '<td>' . $item['persentase'] . '%</td>';
                    break;
                    
                case 'pelayanan':
                    $html .= '<td>' . $item['nama'] . '</td>';
                    $html .= '<td>' . $item['nama_pelayanan'] . '</td>';
                    $html .= '<td>' . $item['jumlah_kegiatan'] . '</td>';
                    break;
        
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>
            <div class="footer">
                Dicetak pada: ' . date('d/m/Y H:i:s') . '<br>
                Oleh: ' . session()->get('fullname') . '
            </div>
        </body>
        </html>';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'laporan_' . $jenisLaporan . '_' . date('Ymd_His') . '.pdf';
        
        $dompdf->stream($filename, array("Attachment" => true));
        exit;
    }

    /**
     * API untuk dashboard mobile
     */
    public function apiDashboardMobile($anggotaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $bulanIni = date('Y-m');
        $tahunIni = date('Y');

        $data = [
            'statistik_bulan_ini' => $this->aktivitasModel->getStatistikIndividu($anggotaId, date('Y-m-01'), date('Y-m-t')),
            'kehadiran_terakhir' => $this->aktivitasModel->getKehadiranTerakhir($anggotaId, 5),
            'jadwal_ibadah_bulan_ini' => $this->ibadahModel->getJadwalBulanIni(),
            'aktivitas_pelayanan_terbaru' => $this->aktivitasModel->getAktivitasPelayananIndividu($anggotaId, date('Y-m-01'), date('Y-m-t'), 5),
            'peringkat' => $this->aktivitasModel->getPeringkatIndividu($anggotaId, $tahunIni)
        ];

        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * API untuk cek kehadiran
     */
    public function apiCekKehadiran($anggotaId, $ibadahId)
    {
        $tanggal = date('Y-m-d');
        
        $hadir = $this->aktivitasModel->where([
            'anggota_id' => $anggotaId,
            'ibadah_id' => $ibadahId,
            'DATE(tanggal)' => $tanggal
        ])->first();

        return $this->respond([
            'status' => 'success',
            'data' => [
                'hadir' => !empty($hadir),
                'waktu_hadir' => $hadir['waktu_hadir'] ?? null,
                'tanggal' => $tanggal
            ]
        ]);
    }

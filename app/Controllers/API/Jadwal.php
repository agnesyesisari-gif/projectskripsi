<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\JadwalIbadahModel;
use App\Models\PelayanModel;

class JadwalIbadah extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\JadwalIbadahModel';
    protected $format = 'json';

    public function __construct()
    {
        $this->jadwalModel = new JadwalIbadahModel();
        $this->pelayanModel = new PelayanModel();
    }

    // GET /api/jadwal-ibadah
    public function index()
    {
        try {
            // Update status otomatis
            $this->jadwalModel->updateStatusOtomatis();
            
            $limit = $this->request->getGet('limit') ?? 20;
            $page = $this->request->getGet('page') ?? 1;
            $search = $this->request->getGet('search');
            
            $builder = $this->jadwalModel;
            
            // Filter pencarian
            if ($search) {
                $builder->groupStart()
                    ->like('pemimpin_ibadah', $search)
                    ->orLike('tema', $search)
                    ->orLike('bacaan_alkitab', $search)
                    ->orLike('tempat', $search)
                    ->groupEnd();
            }
            
            // Filter tanggal
            if ($tanggal = $this->request->getGet('tanggal')) {
                $builder->where('tanggal', $tanggal);
            }
            
            // Filter bulan
            if ($bulan = $this->request->getGet('bulan')) {
                $builder->where('MONTH(tanggal)', $bulan);
            }
            
            // Filter tahun
            if ($tahun = $this->request->getGet('tahun')) {
                $builder->where('YEAR(tanggal)', $tahun);
            }
            
            // Filter jenis ibadah
            if ($jenis = $this->request->getGet('jenis_ibadah')) {
                $builder->where('jenis_ibadah', $jenis);
            }
            
            // Filter status
            if ($status = $this->request->getGet('status')) {
                $builder->where('status', $status);
            }
            
            // Filter pemimpin_ibadah
            if ($pengkhotbah = $this->request->getGet('pemimpin_ibadah')) {
                $builder->where('pemimpin_ibadah', $pemimpin_ibadah);
            }
            
            $total = $builder->countAllResults(false);
            $offset = ($page - 1) * $limit;
            
            $data = $builder->orderBy('tanggal', 'DESC')
                          ->orderBy('waktu', 'ASC')
                          ->findAll($limit, $offset);
            
            // Format data untuk response
            $formattedData = [];
            foreach ($data as $jadwal) {
                $formattedData[] = $this->formatJadwalResponse($jadwal);
            }
            
            return $this->respond([
                'status' => true,
                'message' => 'Data jadwal ibadah berhasil diambil',
                'data' => $formattedData,
                'metadata' => [
                    'total' => $total,
                    'limit' => $limit,
                    'page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'tanggal_server' => date('Y-m-d H:i:s'),
                    'update_otomatis' => 'Status telah diperbarui otomatis'
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/jadwal-ibadah/{id}
    public function show($id = null)
    {
        try {
            $data = $this->jadwalModel->find($id);
            
            if (!$data) {
                return $this->failNotFound('Jadwal ibadah tidak ditemukan');
            }
            
            return $this->respond([
                'status' => true,
                'message' => 'Detail jadwal ibadah',
                'data' => $this->formatJadwalResponse($data),
                'pelayan' => $data->getPelayanArray(),
                'info' => [
                    'hari' => $data->getHari(),
                    'waktu_display' => $data->getWaktuDisplay(),
                    'status_display' => ucfirst($data->status),
                    'status_badge' => $data->getStatusBadge()
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // POST /api/jadwal-ibadah
    public function create()
    {
        try {
            $rules = [
                'tanggal' => 'required|valid_date',
                'waktu' => 'required|valid_date[H:i]',
                'nama_ibadah' => 'required|min_length[3]|max_length[200]',
                'jenis_ibadah' => 'required|max_length[50]',
                'tempat' => 'required|max_length[100]',
                'pemimpin_ibadah' => 'permit_empty',
                'pemusik' => 'max_length[100]',
                'pemandu_pujian' => 'max_length[100]',
                'penatua' => 'max_length[100]',
                'diaken' => 'max_length[100]',
                'tema' => 'max_length[200]',
                'bacaan_alkitab' => 'max_length[200]',
                'keterangan' => 'permit_empty',
                'status' => 'required|in_list[terjadwal,berlangsung,selesai,dibatalkan,tukar_mimbar]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->failValidationErrors($this->validator->getErrors());
            }
            
            // Cek duplikat jadwal di waktu yang sama
            $existing = $this->jadwalModel
                ->where('tanggal', $this->request->getVar('tanggal'))
                ->where('waktu', $this->request->getVar('waktu'))
                ->where('tempat', $this->request->getVar('tempat'))
                ->first();
                
            if ($existing) {
                return $this->fail('Sudah ada jadwal di waktu dan tempat yang sama');
            }
            
            $data = [
                'tanggal' => $this->request->getVar('tanggal'),
                'waktu' => $this->request->getVar('waktu'),
                'jenis_ibadah' => $this->request->getVar('jenis_ibadah'),
                'nama_ibadah' => $this->request->getVar('nama_ibadah'),
                'tempat' => $this->request->getVar('tempat'),
                'pemimpin_ibadah' => $this->request->getVar('pemimpin_ibadah'),
                'pemusik' => $this->request->getVar('pemusik'),
                'pemandu_pujian' => $this->request->getVar('pemandu_pujian'),
                'penatua' => $this->request->getVar('penatua'),
                'diaken' => $this->request->getVar('diaken'),
                'tema' => $this->request->getVar('tema'),
                'bacaan_alkitab' => $this->request->getVar('bacaan_alkitab'),
                'keterangan' => $this->request->getPost('keterangan'),
                'status' => $this->request->getVar('status')
            ];
            
            $jadwalId = $this->jadwalModel->insert($data);
            
            if ($jadwalId) {
                $newData = $this->jadwalModel->find($jadwalId);
                return $this->respondCreated([
                    'status' => true,
                    'message' => 'Jadwal ibadah berhasil ditambahkan',
                    'data' => $this->formatJadwalResponse($newData)
                ]);
            }
            
            return $this->fail('Gagal menambahkan jadwal ibadah');
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // PUT /api/jadwal-ibadah/{id}
    public function update($id = null)
    {
        try {
            $existing = $this->jadwalModel->find($id);
            if (!$existing) {
                return $this->failNotFound('Jadwal ibadah tidak ditemukan');
            }
            
            // Jika status diubah menjadi "berlangsung", cek apakah sudah waktunya
            $data = $this->request->getJSON(true);
            
            if (isset($data['status']) && $data['status'] === 'berlangsung') {
                $now = date('Y-m-d H:i:s');
                $jadwalTime = $existing->tanggal . ' ' . $existing->waktu . ':00';
                
                if (strtotime($jadwalTime) > strtotime($now)) {
                    return $this->fail('Belum waktunya untuk memulai ibadah');
                }
            }
            
            if ($this->jadwalModel->update($id, $data)) {
                $updated = $this->jadwalModel->find($id);
                return $this->respond([
                    'status' => true,
                    'message' => 'Jadwal ibadah berhasil diperbarui',
                    'data' => $this->formatJadwalResponse($updated)
                ]);
            }
            
            return $this->fail('Gagal memperbarui jadwal ibadah');
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // DELETE /api/jadwal-ibadah/{id}
    public function delete($id = null)
    {
        try {
            $existing = $this->jadwalModel->find($id);
            if (!$existing) {
                return $this->failNotFound('Jadwal ibadah tidak ditemukan');
            }
            
            // Cek jika jadwal sudah berlangsung atau selesai
            if (in_array($existing->status, ['berlangsung', 'selesai'])) {
                return $this->fail('Tidak dapat menghapus jadwal yang sudah berlangsung atau selesai');
            }
            
            if ($this->jadwalModel->delete($id)) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Jadwal ibadah berhasil dihapus'
                ]);
            }
            
            return $this->fail('Gagal menghapus jadwal ibadah');
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/jadwal/bulan/{tahun}/{bulan}
    public function getByBulan($tahun, $bulan)
    {
        try {
            $data = $this->jadwalModel->getByBulan($tahun, $bulan);
            
            // Group by tanggal untuk response yang lebih terstruktur
            $groupedData = [];
            foreach ($data as $jadwal) {
                $tanggal = $jadwal->tanggal;
                if (!isset($groupedData[$tanggal])) {
                    $groupedData[$tanggal] = [
                        'tanggal' => $tanggal,
                        'hari' => $jadwal->getHari(),
                        'jadwal' => []
                    ];
                }
                $groupedData[$tanggal]['jadwal'][] = $this->formatJadwalResponse($jadwal);
            }
            
            return $this->respond([
                'status' => true,
                'message' => "Jadwal ibadah bulan $bulan-$tahun",
                'data' => array_values($groupedData),
                'summary' => [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'nama_bulan' => $this->getNamaBulan($bulan),
                    'total_hari_ibadah' => count($groupedData),
                    'total_ibadah' => count($data)
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/jadwal/hari-ini
    public function getHariIni()
    {
        try {
            $this->jadwalModel->updateStatusOtomatis();
            $data = $this->jadwalModel->getHariIni();
            
            return $this->respond([
                'status' => true,
                'message' => 'Jadwal ibadah hari ini',
                'data' => array_map([$this, 'formatJadwalResponse'], $data),
                'info' => [
                    'tanggal' => date('Y-m-d'),
                    'hari' => $this->getHariIndonesia(date('w')),
                    'total_ibadah' => count($data),
                    'selamat' => $this->getSalamW
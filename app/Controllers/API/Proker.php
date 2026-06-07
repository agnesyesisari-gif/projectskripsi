<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProkerModel;
use App\Models\KomisiModel;

class Proker extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\ProkerModel';
    protected $format = 'json';

    public function __construct()
    {
        $this->prokerModel = new ProkerModel();
        $this->komisiModel = new KomisiModel();
    }

    // GET /api/proker
    public function index()
    {
        try {
            $limit = $this->request->getGet('limit') ?? 10;
            $page = $this->request->getGet('page') ?? 1;
            $search = $this->request->getGet('search');
            
            $builder = $this->prokerModel;
            
            // Filter pencarian
            if ($search) {
                $builder->groupStart()
                    ->like('nama_proker', $search)
                    ->orLike('deskripsi', $search)
                    ->orLike('penanggung_jawab', $search)
                    ->groupEnd();
            }
            
            // Filter status
            if ($status = $this->request->getGet('status')) {
                $builder->where('status', $status);
            }
            
            // Filter komisi
            if ($komisi = $this->request->getGet('komisi')) {
                $builder->where('id_komisi', $komisi);
            }
            
            // Filter tanggal
            if ($tahun = $this->request->getGet('tahun')) {
                $builder->where('YEAR(tanggal_mulai)', $tahun);
            }
            
            $total = $builder->countAllResults(false);
            $offset = ($page - 1) * $limit;
            
            $data = $builder->orderBy('created_at', 'DESC')
                          ->findAll($limit, $offset);
            
            // Join dengan data komisi
            foreach ($data as &$item) {
                $komisi = $this->komisiModel->find($item->id_komisi);
                $item->komisi = $komisi ? $komisi['nama_komisi'] : 'Tidak diketahui';
            }
            
            return $this->respond([
                'status' => true,
                'message' => 'Data program kerja berhasil diambil',
                'data' => $data,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'page' => $page,
                    'total_pages' => ceil($total / $limit)
                ],
                'metadata' => [
                    'total_program' => $total,
                    'tahun' => date('Y')
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/proker/{id}
    public function show($id = null)
    {
        try {
            $data = $this->prokerModel->getWithKomisi($id);
            
            if (!$data) {
                return $this->failNotFound('Program kerja tidak ditemukan');
            }
            
            return $this->respond([
                'status' => true,
                'message' => 'Detail program kerja',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // POST /api/proker
    public function create()
    {
        try {
            $rules = [
                'id_komisi' => 'required|numeric',
                'nama_proker' => 'required|max_length[200]',
                'tujuan' => 'required',
                'tanggal_mulai' => 'required|valid_date',
                'tanggal_selesai' => 'required|valid_date',
                'status' => 'required|in_list[perencanaan,berjalan,selesai,dibatalkan]',
                'penanggung_jawab' => 'required|max_length[100]',
                'kontak_pj' => 'permit_empty|max_length[20]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->failValidationErrors($this->validator->getErrors());
            }
            
            // Validasi tanggal
            $tanggalMulai = strtotime($this->request->getVar('tanggal_mulai'));
            $tanggalSelesai = strtotime($this->request->getVar('tanggal_selesai'));
            
            if ($tanggalSelesai < $tanggalMulai) {
                return $this->fail('Tanggal selesai harus setelah tanggal mulai');
            }
            
            $data = [
                'id_komisi' => $this->request->getVar('id_komisi'),
                'nama_proker' => $this->request->getVar('nama_proker'),
                'tujuan' => $this->request->getVar('tujuan'),
                'lokasi' => $this->request->getVar('lokasi'),
                'tanggal_mulai' => $this->request->getVar('tanggal_mulai'),
                'tanggal_selesai' => $this->request->getVar('tanggal_selesai'),
                'anggaran' => $this->request->getVar('anggaran') ?? 0,
                'status' => $this->request->getVar('status'),
                'penanggung_jawab' => $this->request->getVar('penanggung_jawab'),
                'kontak_pj' => $this->request->getVar('kontak_pj'),
                'dokumentasi' => $this->request->getVar('dokumentasi')
            ];
            
            $prokerId = $this->prokerModel->insert($data);
            
            if ($prokerId) {
                $newData = $this->prokerModel->find($prokerId);
                return $this->respondCreated([
                    'status' => true,
                    'message' => 'Program kerja berhasil ditambahkan',
                    'data' => $newData
                ]);
            }
            
            return $this->fail('Gagal menambahkan program kerja');
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // PUT /api/proker/{id}
    public function update($id = null)
    {
        try {
            $existing = $this->prokerModel->find($id);
            if (!$existing) {
                return $this->failNotFound('Program kerja tidak ditemukan');
            }
            
            $data = $this->request->getJSON(true);
            
            // Validasi jika ada data yang dikirim
            if (!empty($data['status'])) {
                $allowedStatus = ['perencanaan', 'berjalan', 'selesai', 'dibatalkan'];
                if (!in_array($data['status'], $allowedStatus)) {
                    return $this->fail('Status tidak valid');
                }
            }
            
            if (!empty($data['tanggal_mulai']) && !empty($data['tanggal_selesai'])) {
                $tanggalMulai = strtotime($data['tanggal_mulai']);
                $tanggalSelesai = strtotime($data['tanggal_selesai']);
                
                if ($tanggalSelesai < $tanggalMulai) {
                    return $this->fail('Tanggal selesai harus setelah tanggal mulai');
                }
            }
            
            if ($this->prokerModel->update($id, $data)) {
                $updated = $this->prokerModel->find($id);
                return $this->respond([
                    'status' => true,
                    'message' => 'Program kerja berhasil diperbarui',
                    'data' => $updated
                ]);
            }
            
            return $this->fail('Gagal memperbarui program kerja');
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // DELETE /api/proker/{id}
    public function delete($id = null)
    {
        try {
            $existing = $this->prokerModel->find($id);
            if (!$existing) {
                return $this->failNotFound('Program kerja tidak ditemukan');
            }
            
            // Soft delete: ubah status menjadi dibatalkan
            $this->prokerModel->update($id, ['status' => 'dibatalkan']);
            
            return $this->respond([
                'status' => true,
                'message' => 'Program kerja berhasil dibatalkan'
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/proker/komisi/{id}
    public function getByKomisi($id_komisi)
    {
        try {
            $data = $this->prokerModel->getByKomisi($id_komisi);
            
            // Hitung statistik
            $total = count($data);
            $totalAnggaran = array_sum(array_column($data, 'anggaran'));
            
            return $this->respond([
                'status' => true,
                'message' => 'Data program kerja per komisi',
                'data' => $data,
                'statistik' => [
                    'total_program' => $total,
                    'total_anggaran' => $totalAnggaran,
                    'rata_anggaran' => $total > 0 ? $totalAnggaran / $total : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/proker/status/{status}
    public function getByStatus($status)
    {
        try {
            $allowedStatus = ['perencanaan', 'berjalan', 'selesai', 'dibatalkan'];
            if (!in_array($status, $allowedStatus)) {
                return $this->fail('Status tidak valid');
            }
            
            $data = $this->prokerModel->getByStatus($status);
            
            return $this->respond([
                'status' => true,
                'message' => "Data program kerja dengan status {$status}",
                'data' => $data,
                'total' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/proker/laporan/anggaran
    public function laporanAnggaran()
    {
        try {
            $data = $this->prokerModel->getLaporanAnggaran();
            
            $totalAnggaran = array_sum(array_column($data, 'total_anggaran'));
            $totalProgram = array_sum(array_column($data, 'total_program'));
            
            return $this->respond([
                'status' => true,
                'message' => 'Laporan anggaran per komisi',
                'data' => $data,
                'summary' => [
                    'total_seluruh_anggaran' => $totalAnggaran,
                    'total_seluruh_program' => $totalProgram,
                    'rata_seluruh_anggaran' => $totalProgram > 0 ? $totalAnggaran / $totalProgram : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // GET /api/proker/calendar
    public function calendar()
    {
        try {
            $tahun = $this->request->getGet('tahun') ?? date('Y');
            $bulan = $this->request->getGet('bulan');
            
            $builder = $this->prokerModel;
            $builder->where('YEAR(tanggal_mulai)', $tahun);
            
            if ($bulan) {
                $builder->where('MONTH(tanggal_mulai)', $bulan);
            }
            
            $data = $builder->orderBy('tanggal_mulai', 'ASC')->findAll();
            
            $calendarData = [];
            foreach ($data as $proker) {
                $calendarData[] = [
                    'id' => $proker->id_proker,
                    'title' => $proker->nama_proker,
                    'start' => $proker->tanggal_mulai,
                    'end' => $proker->tanggal_selesai,
                    'color' => $this->getStatusColor($proker->status),
                    'extendedProps' => [
                        'komisi' => $proker->id_komisi,
                        'lokasi' => $proker->lokasi,
                        'penanggung_jawab' => $proker->penanggung_jawab
                    ]
                ];
            }
            
            return $this->respond([
                'status' => true,
                'message' => 'Data kalender kegiatan',
                'data' => $calendarData
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
    
    private function getStatusColor($status)
    {
        $colors = [
            'perencanaan' => '#ffc107', // Kuning
            'berjalan' => '#17a2b8',    // Biru
            'selesai' => '#28a745',      // Hijau
            'dibatalkan' => '#dc3545'    // Merah
        ];
        
        return $colors[strtolower($status)] ?? '#6c757d';
    }
}
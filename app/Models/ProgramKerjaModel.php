<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramKerjaModel extends Model
{
    protected $table         = 'program_kerja';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama_program','komisi_id','bulan','tahun','anggaran','status','keterangan'];
    protected $useTimestamps = true;

    public function getWithKomisi(array $filter = []): array
    {
        $builder = $this->db->table('program_kerja p')
            ->select('p.*, k.nama_komisi')
            ->join('komisi k', 'p.komisi_id = k.id', 'left');

        if (!empty($filter['komisi_id'])) $builder->where('p.komisi_id', $filter['komisi_id']);
        if (!empty($filter['tahun']))     $builder->where('p.tahun', $filter['tahun']);
        if (!empty($filter['status']))    $builder->where('p.status', $filter['status']);
        if (!empty($filter['search']))    $builder->like('p.nama_program', $filter['search']);

        return $builder->orderBy('p.tahun','DESC')
            ->orderBy('p.bulan','ASC')
            ->get()->getResultArray();
    }

    public function countAktif(): int
    {
        return $this->where('tahun', date('Y'))
            ->where('status !=', 'selesai')
            ->countAllResults();
    }
}

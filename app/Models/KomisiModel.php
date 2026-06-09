<?php

namespace App\Models;

use CodeIgniter\Model;

class KomisiModel extends Model
{
    protected $table         = 'komisi';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama_komisi','ketua','deskripsi'];
    protected $useTimestamps = true;

    public function getAllWithCount(): array
    {
        return $this->db->table('komisi k')
            ->select('k.*, COUNT(p.id) as total_program')
            ->join('program_kerja p', 'k.id = p.komisi_id', 'left')
            ->groupBy('k.id')
            ->orderBy('k.nama_komisi', 'ASC')
            ->get()->getResultArray();
    }
}

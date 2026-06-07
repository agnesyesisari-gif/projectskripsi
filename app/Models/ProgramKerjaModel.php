<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramKerjaModel extends Model
{
    protected $table = 'program_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = ['komisi_id', 'nama_program', 'deskripsi', 'tanggal_mulai', 'tanggal_selesai', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByKomisi($komisiId)
    {
        return $this->where('komisi_id', $komisiId)->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class PengumumanModel extends Model
{
    protected $table         = 'pengumuman';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['judul','isi','tanggal_tayang','status','created_by'];
    protected $useTimestamps = true;
}

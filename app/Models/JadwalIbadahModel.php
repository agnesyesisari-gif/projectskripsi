<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalIbadahModel extends Model
{
    protected $table = 'jadwal_ibadah';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_ibadah', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'lokasi', 'keterangan', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

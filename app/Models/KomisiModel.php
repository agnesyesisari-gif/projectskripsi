<?php

namespace App\Models;

use CodeIgniter\Model;

class KomisiModel extends Model
{
    protected $table = 'komisi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'deskripsi', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

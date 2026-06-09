<?php

namespace App\Models;

use CodeIgniter\Model;

class WaLogModel extends Model
{
    protected $table         = 'wa_log';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nomor','pesan','status'];
    protected $useTimestamps = false;

    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getRecent(int $limit = 50): array
    {
        return $this->orderBy('created_at','DESC')->limit($limit)->findAll();
    }
}

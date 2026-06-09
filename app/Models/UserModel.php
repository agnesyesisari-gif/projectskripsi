<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama','username','password','role','status'];
    protected $useTimestamps = true;

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->where('status', 1)->first();
    }
}

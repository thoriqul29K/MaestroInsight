<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'tb_user';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['username', 'password', 'nama_lengkap', 'role'];

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }
}

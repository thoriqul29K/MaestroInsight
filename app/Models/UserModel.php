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

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    public function findAllUsers(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    public function createUser(array $data): int
    {
        return $this->insert($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->delete($id);
    }

    public function isUsernameUnique(string $username, ?int $excludeId = null): bool
    {
        $builder = $this->where('username', $username);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->first() === null;
    }
}

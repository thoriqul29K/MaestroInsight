<?php

namespace App\Models;

use CodeIgniter\Model;

class RfmModel extends Model
{
    protected $table         = 'tb_rfm';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['id_pelanggan', 'recency', 'frequency', 'monetary', 'recency_norm', 'frequency_norm', 'monetary_norm', 'created_at'];

    public function getByPelanggan(int $idPelanggan): ?array
    {
        return $this->where('id_pelanggan', $idPelanggan)->first();
    }

    public function getAllWithPelanggan(): array
    {
        return $this->select('tb_rfm.*, tb_pelanggan.nama_pelanggan, tb_pelanggan.segment, tb_pelanggan.email, tb_pelanggan.telepon')
            ->join('tb_pelanggan', 'tb_pelanggan.id = tb_rfm.id_pelanggan', 'left')
            ->findAll();
    }
}

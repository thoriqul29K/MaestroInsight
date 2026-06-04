<?php

namespace App\Models;

use CodeIgniter\Model;

class PelangganModel extends Model
{
    protected $table         = 'tb_pelanggan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nama_pelanggan', 'email', 'telepon', 'alamat', 'segment'];

    public function getAllWithSegment(): array
    {
        return $this->orderBy('nama_pelanggan', 'ASC')->findAll();
    }

    public function countBySegment(): array
    {
        $rows = $this->select('segment, COUNT(*) as total')
            ->groupBy('segment')
            ->findAll();

        $result = [
            'loyal' => 0, 'potential' => 0, 'budget' => 0,
            'seasonal' => 0, 'at_risk' => 0, 'belum' => 0,
        ];
        foreach ($rows as $r) {
            $key = $r['segment'] ?: 'belum';
            $result[$key] = (int) $r['total'];
        }
        return $result;
    }
}

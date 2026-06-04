<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table         = 'tb_transaksi';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['id_pelanggan', 'tanggal_transaksi', 'layanan', 'tujuan', 'jumlah_transaksi'];

    public function getAllWithPelanggan(): array
    {
        return $this->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
            ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left')
            ->orderBy('tb_transaksi.tanggal_transaksi', 'DESC')
            ->findAll();
    }

    public function getByPelanggan(int $idPelanggan): array
    {
        return $this->where('id_pelanggan', $idPelanggan)
            ->orderBy('tanggal_transaksi', 'DESC')
            ->findAll();
    }

    public function countAll(): int
    {
        return $this->countAll();
    }

    public function getRecent(int $limit = 5): array
    {
        return $this->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
            ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left')
            ->orderBy('tb_transaksi.tanggal_transaksi', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}

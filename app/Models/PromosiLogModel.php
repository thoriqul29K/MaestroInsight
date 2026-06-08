<?php

namespace App\Models;

use CodeIgniter\Model;

class PromosiLogModel extends Model
{
    protected $table         = 'tb_promosi_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_pelanggan',
        'nama_pelanggan',
        'email_target',
        'channel',
        'subject',
        'status',
        'error_message',
        'attachment_filename',
    ];

    public function getFiltered(?int $limit = 50, ?string $status = null, ?string $channel = null, ?int $offset = null): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if ($status !== null && $status !== '') {
            $builder->where('status', $status);
        }

        if ($channel !== null && $channel !== '') {
            $builder->where('channel', $channel);
        }

        if ($limit !== null) {
            $builder->limit($limit, $offset ?? 0);
        }

        return $builder->findAll();
    }

    public function countByStatus(?string $since = null): array
    {
        $builder = $this->select('status, COUNT(*) as total')->groupBy('status');

        if ($since !== null) {
            $builder->where('created_at >=', $since);
        }

        $rows = $builder->findAll();
        $result = ['success' => 0, 'failed' => 0];

        foreach ($rows as $r) {
            $result[$r['status']] = (int) $r['total'];
        }

        return $result;
    }

    public function countFiltered(?string $status = null, ?string $channel = null): int
    {
        $builder = $this;

        if ($status !== null && $status !== '') {
            $builder = $builder->where('status', $status);
        }
        if ($channel !== null && $channel !== '') {
            $builder = $builder->where('channel', $channel);
        }

        return $builder->countAllResults();
    }

    public function deleteFiltered(?string $status = null, ?string $channel = null): int
    {
        $builder = $this;

        if ($status !== null && $status !== '') {
            $builder = $builder->where('status', $status);
        }
        if ($channel !== null && $channel !== '') {
            $builder = $builder->where('channel', $channel);
        }

        return $builder->delete();
    }
}

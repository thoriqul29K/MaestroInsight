<?php

namespace App\Services;

use App\Models\RfmModel;
use App\Models\TransaksiModel;

class RfmService
{
    protected TransaksiModel $transaksi;
    protected RfmModel $rfm;

    public function __construct()
    {
        $this->transaksi = new TransaksiModel();
        $this->rfm       = new RfmModel();
    }

    public function hitungRFM(): array
    {
        $db = \Config\Database::connect();

        $sql = "
            SELECT
                p.id AS id_pelanggan,
                COALESCE(MAX(t.tanggal_transaksi), '1970-01-01') AS tanggal_terakhir,
                COALESCE(COUNT(t.id), 0) AS frequency,
                COALESCE(SUM(t.jumlah_transaksi), 0) AS monetary
            FROM tb_pelanggan p
            LEFT JOIN tb_transaksi t ON t.id_pelanggan = p.id
            GROUP BY p.id
        ";

        $rows = $db->query($sql)->getResultArray();

        $recencies  = [];
        $frequencies = [];
        $monetaries = [];
        $data = [];

        foreach ($rows as $r) {
            $tanggalAkhir = strtotime($r['tanggal_terakhir']);
            $today        = strtotime(date('Y-m-d'));
            $recency      = ($r['frequency'] > 0) ? max(0, (int) (($today - $tanggalAkhir) / 86400)) : 9999;

            $data[] = [
                'id_pelanggan' => (int) $r['id_pelanggan'],
                'recency'      => $recency,
                'frequency'    => (int) $r['frequency'],
                'monetary'     => (int) $r['monetary'],
            ];

            $recencies[]   = $recency;
            $frequencies[] = (int) $r['frequency'];
            $monetaries[]  = (int) $r['monetary'];
        }

        if (empty($data)) {
            return ['status' => 'empty', 'count' => 0];
        }

        $rMin = min($recencies); $rMax = max($recencies);
        $fMin = min($frequencies); $fMax = max($frequencies);
        $mMin = min($monetaries); $mMax = max($monetaries);

        $this->rfm->db->table('tb_rfm')->truncate();

        $insertData = [];
        $now = date('Y-m-d H:i:s');
        foreach ($data as $d) {
            $insertData[] = [
                'id_pelanggan'   => $d['id_pelanggan'],
                'recency'        => $d['recency'],
                'frequency'      => $d['frequency'],
                'monetary'       => $d['monetary'],
                'recency_norm'   => $this->normalize($d['recency'], $rMin, $rMax),
                'frequency_norm' => $this->normalize($d['frequency'], $fMin, $fMax),
                'monetary_norm'  => $this->normalize($d['monetary'], $mMin, $mMax),
                'created_at'     => $now,
            ];
        }

        $this->rfm->insertBatch($insertData);

        return [
            'status' => 'ok',
            'count'  => count($insertData),
        ];
    }

    private function normalize(float $val, float $min, float $max): float
    {
        if ($max == $min) {
            return 0.0;
        }
        return round(($val - $min) / ($max - $min), 6);
    }

    public function exportToCSV(string $path): bool
    {
        $data = $this->rfm->findAll();

        $dir = dirname($path);
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
                log_message('error', "[RfmService::exportToCSV] Gagal membuat direktori: {$dir}");
                return false;
            }
        }

        $fh = @fopen($path, 'w');
        if ($fh === false) {
            $err = error_get_last()['message'] ?? 'unknown';
            log_message('error', "[RfmService::exportToCSV] Gagal membuka file untuk ditulis: {$path}. Error: {$err}");
            return false;
        }

        fputcsv($fh, ['id_pelanggan', 'recency', 'frequency', 'monetary']);
        foreach ($data as $row) {
            fputcsv($fh, [$row['id_pelanggan'], $row['recency'], $row['frequency'], $row['monetary']]);
        }
        fclose($fh);

        if (! is_file($path) || filesize($path) === 0) {
            log_message('error', "[RfmService::exportToCSV] File CSV kosong/tidak ada setelah tulis: {$path}");
            return false;
        }

        return true;
    }

    public function importSegmentResults(string $csvPath): int
    {
        $db = \Config\Database::connect();
        $count = 0;
        $fh = fopen($csvPath, 'r');
        if (! $fh) {
            return 0;
        }
        $header = fgetcsv($fh);
        while (($row = fgetcsv($fh)) !== false) {
            $data = array_combine($header, $row);
            $segment = $data['segment'] ?? null;
            if (! in_array($segment, ['loyal', 'potential', 'budget', 'seasonal', 'at_risk'], true)) {
                $segment = null;
            }
            $db->table('tb_pelanggan')
                ->where('id', $data['id_pelanggan'])
                ->update(['segment' => $segment]);
            $count++;
        }
        fclose($fh);
        return $count;
    }
}

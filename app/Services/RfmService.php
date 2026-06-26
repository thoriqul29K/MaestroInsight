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
            INNER JOIN tb_transaksi t ON t.id_pelanggan = p.id
            GROUP BY p.id
        ";

        $rows = $db->query($sql)->getResultArray();

        $data = [];

        foreach ($rows as $r) {
            $tanggalAkhir = strtotime($r['tanggal_terakhir']);
            $today        = strtotime(date('Y-m-d'));
            $recency      = max(0, (int) (($today - $tanggalAkhir) / 86400));

            $data[] = [
                'id_pelanggan' => (int) $r['id_pelanggan'],
                'recency'      => $recency,
                'frequency'    => (int) $r['frequency'],
                'monetary'     => (int) $r['monetary'],
            ];
        }

        if (empty($data)) {
            return ['status' => 'empty', 'count' => 0];
        }

        $this->rfm->db->table('tb_rfm')->truncate();

        $insertData = [];
        $now = date('Y-m-d H:i:s');
        foreach ($data as $d) {
            $insertData[] = [
                'id_pelanggan' => $d['id_pelanggan'],
                'recency'      => $d['recency'],
                'frequency'    => $d['frequency'],
                'monetary'     => $d['monetary'],
                'created_at'   => $now,
            ];
        }

        $this->rfm->insertBatch($insertData);

        return [
            'status' => 'ok',
            'count'  => count($insertData),
        ];
    }

    public function exportToCSV(string $path): bool
    {
        $data = $this->rfm->findAll();

        $dir = dirname($path);
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
                return false;
            }
        }

        $fh = @fopen($path, 'w');
        if ($fh === false) {
            return false;
        }

        fputcsv($fh, ['id_pelanggan', 'recency', 'frequency', 'monetary']);
        foreach ($data as $row) {
            fputcsv($fh, [$row['id_pelanggan'], $row['recency'], $row['frequency'], $row['monetary']]);
        }
        fclose($fh);

        if (! is_file($path) || filesize($path) === 0) {
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
        $hasNorm = in_array('recency_norm', $header, true)
            && in_array('frequency_norm', $header, true)
            && in_array('monetary_norm', $header, true);

        while (($row = fgetcsv($fh)) !== false) {
            $data = array_combine($header, $row);
            $idPelanggan = (int) ($data['id_pelanggan'] ?? 0);
            if ($idPelanggan <= 0) {
                continue;
            }

            $segment = $data['segment'] ?? null;
            if (! in_array($segment, ['loyal', 'potential', 'budget', 'seasonal', 'at_risk'], true)) {
                $segment = null;
            }
            $db->table('tb_pelanggan')
                ->where('id', $idPelanggan)
                ->update(['segment' => $segment]);

            if ($hasNorm) {
                $normUpdate = [
                    'recency_norm'   => (float) ($data['recency_norm']   ?? 0),
                    'frequency_norm' => (float) ($data['frequency_norm'] ?? 0),
                    'monetary_norm'  => (float) ($data['monetary_norm']  ?? 0),
                ];
                $db->table('tb_rfm')
                    ->where('id_pelanggan', $idPelanggan)
                    ->update($normUpdate);
            }

            $count++;
        }
        fclose($fh);
        return $count;
    }

    public function refineSeasonalSegments(float $threshold = 0.7): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('tb_transaksi t')
            ->select('t.id_pelanggan, MONTH(t.tanggal_transaksi) AS bulan, p.segment')
            ->join('tb_pelanggan p', 'p.id = t.id_pelanggan', 'left')
            ->orderBy('t.id_pelanggan')
            ->get()
            ->getResultArray();

        $pelangganData = [];
        foreach ($rows as $r) {
            $id = (int) $r['id_pelanggan'];
            if (! isset($pelangganData[$id])) {
                $pelangganData[$id] = ['bulan' => [], 'segment' => $r['segment']];
            }
            $pelangganData[$id]['bulan'][] = (int) $r['bulan'];
        }

        $refinedCount = 0;
        foreach ($pelangganData as $idPelanggan => $data) {
            $currentSegment = $data['segment'];

            if (in_array($currentSegment, ['loyal', 'at_risk'], true)) {
                continue;
            }

            $totalTx = count($data['bulan']);
            if ($totalTx < 2) {
                continue;
            }

            $monthCounts = array_count_values($data['bulan']);
            $maxMonthCount = max($monthCounts);
            $peakRatio = $maxMonthCount / $totalTx;

            if ($peakRatio >= $threshold) {
                $db->table('tb_pelanggan')
                    ->where('id', $idPelanggan)
                    ->update(['segment' => 'seasonal']);
                $refinedCount++;
            }
        }

        return ['refined' => $refinedCount];
    }
}

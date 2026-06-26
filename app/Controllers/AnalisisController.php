<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use App\Models\RfmModel;
use App\Services\RfmService;

class AnalisisController extends BaseController
{
    protected RfmService $rfm;
    protected RfmModel $rfmModel;
    protected PelangganModel $pelanggan;

    public function __construct()
    {
        $this->rfm      = new RfmService();
        $this->rfmModel = new RfmModel();
        $this->pelanggan = new PelangganModel();
    }

    public function index()
    {
        $sort    = $this->request->getGet('sort') ?? 'recency';
        $order   = $this->request->getGet('order') ?? 'ASC';
        $perPage = $this->request->getGet('per_page');

        $sortMap = [
            'id'              => 'tb_pelanggan.id',
            'nama_pelanggan'  => 'tb_pelanggan.nama_pelanggan',
            'recency'         => 'tb_rfm.recency',
            'frequency'       => 'tb_rfm.frequency',
            'monetary'        => 'tb_rfm.monetary',
            'segment'         => 'tb_pelanggan.segment',
        ];
        $allowedSort = array_keys($sortMap);
        $sort  = in_array($sort, $allowedSort, true) ? $sort : 'recency';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        if (is_numeric($perPage)) {
            $perPage = (int) $perPage;
        }
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 100;

        $selectCols = 'tb_rfm.*, tb_pelanggan.nama_pelanggan, tb_pelanggan.segment, tb_pelanggan.email, tb_pelanggan.telepon';
        $sortCol    = $sortMap[$sort];

        if ($perPage === 'all') {
            $rfm = $this->rfmModel
                ->select($selectCols)
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_rfm.id_pelanggan', 'left')
                ->orderBy($sortCol, $order)
                ->findAll();
            $pager = null;
        } else {
            $page = (int) ($this->request->getGet('page') ?: 1);
            $page = max($page, 1);

            $this->rfmModel
                ->select($selectCols)
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_rfm.id_pelanggan', 'left')
                ->orderBy($sortCol, $order);

            $total  = $this->rfmModel->countAllResults(false);
            $offset = ($page - 1) * (int) $perPage;
            $rfm    = $this->rfmModel->findAll((int) $perPage, $offset);

            $pager = service('pager');
            $pager->store('default', $page, (int) $perPage, $total, 0);
            $pager->only(['sort', 'order', 'per_page']);
        }

        $data = [
            'title'     => 'Analisis RFM',
            'pageTitle' => 'Analisis Data Pelanggan',
            'pageIcon'  => 'bi-graph-up',
            'rfm'       => $rfm,
            'pager'     => $pager,
            'sort'      => $sort,
            'order'     => $order,
            'perPage'   => $perPage,
            'ringkasan' => $this->pelanggan->countBySegment(),
        ];
        return view('pages/analisis/index', $data);
    }

    public function data()
    {
        $sortMap = [
            'id'              => 'tb_pelanggan.id',
            'nama_pelanggan'  => 'tb_pelanggan.nama_pelanggan',
            'recency'         => 'tb_rfm.recency',
            'frequency'       => 'tb_rfm.frequency',
            'monetary'        => 'tb_rfm.monetary',
            'segment'         => 'tb_pelanggan.segment',
        ];
        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        $allowedDir     = ['asc', 'desc'];

        $sortKey = (string) ($this->request->getGet('sort') ?? 'id');
        $sort    = isset($sortMap[$sortKey]) ? $sortMap[$sortKey] : 'tb_pelanggan.id';
        if (! isset($sortMap[$sortKey])) {
            $sortKey = 'id';
        }

        $dir = strtolower((string) ($this->request->getGet('dir') ?? 'asc'));
        if (! in_array($dir, $allowedDir, true)) {
            $dir = 'asc';
        }

        $perPageRaw = $this->request->getGet('per_page');
        if (is_numeric($perPageRaw)) {
            $perPage = (int) $perPageRaw;
        } else {
            $perPage = (string) $perPageRaw;
        }
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 100;
        }

        $page = max((int) ($this->request->getGet('page') ?: 1), 1);

        $selectCols = 'tb_rfm.*, tb_pelanggan.nama_pelanggan, tb_pelanggan.segment, tb_pelanggan.email, tb_pelanggan.telepon';

        $builder = $this->rfmModel
            ->select($selectCols)
            ->join('tb_pelanggan', 'tb_pelanggan.id = tb_rfm.id_pelanggan', 'left');

        $total = $builder->countAllResults(true);

        if ($perPage === 'all') {
            $rows = $this->rfmModel
                ->select($selectCols)
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_rfm.id_pelanggan', 'left')
                ->orderBy($sort, $dir)
                ->findAll();
            $totalPages = $total > 0 ? 1 : 0;
            $page       = 1;
        } else {
            $offset     = ($page - 1) * (int) $perPage;
            $rows       = $this->rfmModel
                ->select($selectCols)
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_rfm.id_pelanggan', 'left')
                ->orderBy($sort, $dir)
                ->findAll((int) $perPage - 1, $offset);
            $totalPages = (int) ceil($total / (int) $perPage);
        }

        return $this->response->setJSON([
            'rows'        => $rows,
            'total'       => (int) $total,
            'page'        => (int) $page,
            'per_page'    => $perPage === 'all' ? 'all' : (int) $perPage,
            'total_pages' => (int) $totalPages,
            'sort'        => $sortKey,
            'dir'         => $dir,
        ]);
    }

    public function prosesRFMCluster()
    {
        $timeoutSeconds = (int) (env('CLUSTERING_TIMEOUT', 300));

        @session_write_close();
        @set_time_limit(0);
        @ignore_user_abort(true);

        $this->writeProgress(5, 'rfm', 'Menghitung Recency, Frequency, Monetary...');

        $rfmResult = $this->rfm->hitungRFM();
        if (($rfmResult['status'] ?? '') === 'empty') {
            $this->writeProgress(100, 'error', 'Tidak ada data pelanggan untuk dianalisis.');
            return redirect()->to('/analisis')->with('error', 'Tidak ada data pelanggan untuk dianalisis.');
        }

        $rfmCount = (int) ($rfmResult['count'] ?? 0);
        $this->writeProgress(30, 'rfm', "RFM selesai dihitung untuk {$rfmCount} pelanggan.");

        $this->writeProgress(35, 'clustering', 'Mengekspor data ke CSV...');
        $clusterResult = $this->runClustering($timeoutSeconds);

        if (! empty($clusterResult['error'])) {
            $this->writeProgress(100, 'error', $clusterResult['error']);
            return redirect()->to('/analisis')->with('error', $clusterResult['error']);
        }

        $segCount = (int) ($clusterResult['count'] ?? 0);
        $this->writeProgress(100, 'done', 'Proses selesai.');

        return redirect()->to('/analisis')->with(
            'success',
            "RFM dihitung untuk {$rfmCount} pelanggan dan segmentasi berhasil untuk {$segCount} pelanggan."
        );
    }

    public function progress()
    {
        return $this->response->setJSON($this->readProgress());
    }

    private function runClustering(int $timeoutSeconds): array
    {
        $writable = WRITEPATH . 'uploads';
        if (! is_dir($writable)) {
            @mkdir($writable, 0755, true);
        }
        $in  = $writable . DIRECTORY_SEPARATOR . 'rfm_input.csv';
        $out = $writable . DIRECTORY_SEPARATOR . 'rfm_output.csv';

        $exported = $this->rfm->exportToCSV($in);
        if (! $exported || ! is_file($in)) {
            return [
                'error' => 'Gagal mengekspor data RFM ke CSV.',
            ];
        }
        $this->writeProgress(50, 'clustering', 'Data diekspor. Menjalankan Python hierarchical clustering...');

        $python = $this->findPython();
        $script = ROOTPATH . 'app' . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'clustering.py';

        if (! $python || ! file_exists($script)) {
            @unlink($in);
            return ['error' => 'Python atau script clustering tidak ditemukan.'];
        }

        $cmd = escapeshellarg($python)
            . ' ' . escapeshellarg($script)
            . ' ' . escapeshellarg($in)
            . ' ' . escapeshellarg($out)
            . ' ' . escapeshellarg('--timeout')
            . ' ' . escapeshellarg((string) $timeoutSeconds)
            . ' 2>&1';

        $result = $this->runWithTimeout($cmd, $timeoutSeconds);
        $output = $result['output'];

        if ($result['timed_out']) {
            return [
                'error' => "Clustering memakan waktu lebih dari {$timeoutSeconds} detik dan dihentikan otomatis. Coba perkecil dataset atau naikkan CLUSTERING_TIMEOUT di .env.",
            ];
        }

        if (! file_exists($out)) {
            @unlink($in);
            $snippet = trim($output) !== '' ? substr($output, 0, 500) : '(kosong)';
            return [
                'error' => "Gagal menjalankan clustering. Output Python: {$snippet}",
            ];
        }
        $this->writeProgress(85, 'clustering', 'Mengimpor hasil segmentasi...');
        $count = $this->rfm->importSegmentResults($out);

        $this->writeProgress(92, 'clustering', 'Menganalisis pola bulan transaksi (Phase 2)...');
        $threshold = (float) (env('SEASONAL_PEAK_THRESHOLD', 0.7));
        $refineResult = $this->rfm->refineSeasonalSegments($threshold);
        $refined = (int) ($refineResult['refined'] ?? 0);

        $this->writeProgress(95, 'clustering', "Berhasil mensegmentasi {$count} pelanggan. {$refined} pelanggan diidentifikasi sebagai seasonal.");

        return ['count' => $count];
    }

    private function writeProgress(int $percent, string $stage, string $detail): void
    {
        $writable = WRITEPATH . 'uploads';
        if (! is_dir($writable)) {
            @mkdir($writable, 0755, true);
        }
        $payload = [
            'percent' => max(0, min(100, $percent)),
            'stage'   => $stage,
            'detail'  => $detail,
            'time'    => time(),
        ];
        @file_put_contents(
            $writable . DIRECTORY_SEPARATOR . 'clustering_progress.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
    }

    private function readProgress(): array
    {
        $file = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clustering_progress.json';
        if (! is_file($file)) {
            return ['percent' => 0, 'stage' => 'idle', 'detail' => ''];
        }
        $raw = @file_get_contents($file);
        $data = json_decode((string) $raw, true);
        if (! is_array($data)) {
            return ['percent' => 0, 'stage' => 'idle', 'detail' => ''];
        }
        return [
            'percent' => (int) ($data['percent'] ?? 0),
            'stage'   => (string) ($data['stage'] ?? 'idle'),
            'detail'  => (string) ($data['detail'] ?? ''),
        ];
    }

    private function runWithTimeout(string $cmd, int $timeoutSeconds): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $pipes = [];
        $proc  = @proc_open($cmd, $descriptors, $pipes);

        if (! is_resource($proc)) {
            return [
                'timed_out' => false,
                'output'    => 'Tidak dapat memulai proses Python.',
            ];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $start  = time();
        $status = null;

        while (true) {
            $status = proc_get_status($proc);
            $output .= (string) stream_get_contents($pipes[1]);
            $output .= (string) stream_get_contents($pipes[2]);

            if (! $status['running']) {
                $output .= (string) stream_get_contents($pipes[1]);
                $output .= (string) stream_get_contents($pipes[2]);
                break;
            }

            if ((time() - $start) >= $timeoutSeconds) {
                $this->terminateProcess($proc, $pipes);
                return [
                    'timed_out' => true,
                    'output'    => $output,
                ];
            }

            usleep(100000);
        }

        foreach ($pipes as $p) {
            if (is_resource($p)) {
                fclose($p);
            }
        }
        proc_close($proc);

        return [
            'timed_out' => false,
            'output'    => $output,
        ];
    }

    private function findPython(): ?string
    {
        $candidates = ['python', 'python3', 'py'];
        foreach ($candidates as $cmd) {
            $raw = trim((string) shell_exec("where $cmd 2>nul"));
            if ($raw === '') {
                continue;
            }
            $lines = preg_split('/\r\n|\r|\n/', $raw);
            $first = trim((string) ($lines[0] ?? ''));
            if ($first !== '') {
                return $first;
            }
        }
        return null;
    }

    private function terminateProcess($proc, array $pipes): void
    {
        if (is_resource($proc)) {
            @proc_terminate($proc, 9);
        }
        foreach ($pipes as $p) {
            if (is_resource($p)) {
                @fclose($p);
            }
        }
        if (is_resource($proc)) {
            @proc_close($proc);
        }
    }
}

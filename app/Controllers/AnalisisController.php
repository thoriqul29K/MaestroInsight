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
        $data = [
            'title'     => 'Analisis RFM',
            'pageTitle' => 'Analisis Data Pelanggan',
            'pageIcon'  => 'bi-graph-up',
            'rfm'       => $this->rfmModel->getAllWithPelanggan(),
            'ringkasan' => $this->pelanggan->countBySegment(),
        ];
        return view('pages/analisis/index', $data);
    }

    public function prosesRFM()
    {
        $result = $this->rfm->hitungRFM();
        if (($result['status'] ?? '') === 'empty') {
            return redirect()->to('/analisis')->with('error', 'Tidak ada data pelanggan untuk dianalisis.');
        }
        return redirect()->to('/analisis')->with('success', "RFM berhasil dihitung untuk {$result['count']} pelanggan.");
    }

    public function prosesSegmentasi()
    {
        $timeoutSeconds = (int) (env('CLUSTERING_TIMEOUT', 300));

        @session_write_close();
        @set_time_limit(0);
        @ignore_user_abort(true);

        $writable = WRITEPATH . 'uploads';
        if (! is_dir($writable)) {
            mkdir($writable, 0755, true);
        }
        $in  = $writable . DIRECTORY_SEPARATOR . 'rfm_input.csv';
        $out = $writable . DIRECTORY_SEPARATOR . 'rfm_output.csv';

        $this->rfm->exportToCSV($in);

        $python = $this->findPython();
        $script = ROOTPATH . 'app' . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'clustering.py';

        if (! $python || ! file_exists($script)) {
            @unlink($in);
            return redirect()->to('/analisis')->with('error', 'Python atau script clustering tidak ditemukan.');
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
            @unlink($in);
            @unlink($out);
            return redirect()->to('/analisis')->with(
                'error',
                "Clustering memakan waktu lebih dari {$timeoutSeconds} detik dan dihentikan otomatis. Coba perkecil dataset atau naikkan CLUSTERING_TIMEOUT di .env."
            );
        }

        if (! file_exists($out)) {
            @unlink($in);
            return redirect()->to('/analisis')->with('error', 'Gagal menjalankan clustering. Output: ' . $output);
        }

        $count = $this->rfm->importSegmentResults($out);

        @unlink($in);
        @unlink($out);

        return redirect()->to('/analisis')->with('success', "Segmentasi berhasil untuk {$count} pelanggan.");
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
            $path = trim(shell_exec("where $cmd 2>nul") ?? '');
            if (! empty($path)) {
                return $path;
            }
        }
        return null;
    }
}

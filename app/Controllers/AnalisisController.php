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
            'pageTitle' => 'Analisis RFM & Segmentasi',
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
            return redirect()->to('/analisis')->with('error', 'Python atau script clustering tidak ditemukan.');
        }

        $cmd = escapeshellarg($python) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($in) . ' ' . escapeshellarg($out) . ' 2>&1';
        $output = shell_exec($cmd);

        if (! file_exists($out)) {
            return redirect()->to('/analisis')->with('error', 'Gagal menjalankan clustering. Output: ' . $output);
        }

        $count = $this->rfm->importSegmentResults($out);

        @unlink($in);
        @unlink($out);

        return redirect()->to('/analisis')->with('success', "Segmentasi berhasil untuk {$count} pelanggan.");
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

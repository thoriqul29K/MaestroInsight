<?php

namespace App\Libraries\Rfm;

class HostRfmRunner implements RfmRunnerInterface
{
    public function run(string $inputCsv, string $outputCsv, int $timeoutSeconds): array
    {
        $python = $this->findPython();
        $script = ROOTPATH . 'app' . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'clustering.py';

        if (! $python || ! file_exists($script)) {
            return [
                'output'    => 'Python atau script clustering tidak ditemukan.',
                'exit_code' => 127,
                'timed_out' => false,
            ];
        }

        $cmd = escapeshellarg($python)
            . ' ' . escapeshellarg($script)
            . ' ' . escapeshellarg($inputCsv)
            . ' ' . escapeshellarg($outputCsv)
            . ' ' . escapeshellarg('--timeout')
            . ' ' . escapeshellarg((string) $timeoutSeconds)
            . ' 2>&1';

        return $this->runWithTimeout($cmd, $timeoutSeconds);
    }

    private function findPython(): ?string
    {
        $isWindows = stripos(PHP_OS, 'WIN') === 0;
        $candidates = ['python', 'python3', 'py'];

        foreach ($candidates as $cmd) {
            $probe = $isWindows
                ? trim((string) shell_exec("where $cmd 2>nul"))
                : trim((string) shell_exec("command -v $cmd 2>/dev/null"));
            if ($probe === '') {
                continue;
            }
            $lines = preg_split('/\r\n|\r|\n/', $probe);
            $first = trim((string) ($lines[0] ?? ''));
            if ($first !== '') {
                return $first;
            }
        }
        return null;
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
                'output'    => 'Tidak dapat memulai proses Python.',
                'exit_code' => 1,
                'timed_out' => false,
            ];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $start  = time();

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
                $this->terminate($proc, $pipes);
                return [
                    'output'    => $output,
                    'exit_code' => 124,
                    'timed_out' => true,
                ];
            }

            usleep(100000);
        }

        $exitCode = $status['exitcode'] ?? 0;

        foreach ($pipes as $p) {
            if (is_resource($p)) {
                fclose($p);
            }
        }
        proc_close($proc);

        return [
            'output'    => $output,
            'exit_code' => $exitCode,
            'timed_out' => false,
        ];
    }

    private function terminate($proc, array $pipes): void
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
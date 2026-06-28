<?php

namespace App\Libraries\Rfm;

class DockerRfmRunner implements RfmRunnerInterface
{
    private string $container;
    private string $scriptPath;

    public function __construct(?string $container = null, ?string $scriptPath = null)
    {
        $envContainer  = getenv('PYTHON_CONTAINER');
        $envScriptPath = getenv('PYTHON_SCRIPT_PATH');

        $this->container  = $container  ?: ($envContainer  ?: 'maestro_python');
        $this->scriptPath = $scriptPath ?: ($envScriptPath ?: '/app/clustering.py');
    }

    public function run(string $inputCsv, string $outputCsv, int $timeoutSeconds): array
    {
        if (! $this->checkDockerCli()) {
            return [
                'output'    => 'docker CLI tidak tersedia di container app. Pasang docker-cli atau set DOCKER_CLUSTER=0.',
                'exit_code' => 127,
                'timed_out' => false,
            ];
        }

        $containerInput  = '/data/rfm_input.csv';
        $containerOutput = '/data/rfm_output.csv';

        $dockerBin = trim((string) shell_exec('command -v docker 2>/dev/null')) ?: 'docker';

        $copyIn  = $dockerBin . ' cp '
            . escapeshellarg($inputCsv) . ' '
            . escapeshellarg($this->container . ':' . $containerInput) . ' 2>&1';
        $this->execSimple($copyIn);

        $execCmd = sprintf(
            '%s exec %s python %s %s %s --timeout %d 2>&1',
            $dockerBin,
            escapeshellarg($this->container),
            escapeshellarg($this->scriptPath),
            escapeshellarg($containerInput),
            escapeshellarg($containerOutput),
            $timeoutSeconds
        );

        $result = $this->runWithTimeout($execCmd, $timeoutSeconds);

        $copyOut = $dockerBin . ' cp '
            . escapeshellarg($this->container . ':' . $containerOutput) . ' '
            . escapeshellarg($outputCsv) . ' 2>&1';
        $this->execSimple($copyOut);

        return $result;
    }

    private function checkDockerCli(): bool
    {
        $probe = trim((string) shell_exec('command -v docker 2>/dev/null'));
        return $probe !== '';
    }

    private function execSimple(string $cmd): void
    {
        @shell_exec($cmd);
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
                'output'    => 'Tidak dapat memulai proses docker exec.',
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
}
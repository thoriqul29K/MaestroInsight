<?php

namespace App\Libraries\Rfm;

interface RfmRunnerInterface
{
    /**
     * Jalankan skrip clustering terhadap CSV input dan tulis hasil ke CSV output.
     *
     * @param string $inputCsv  Path absolut ke CSV input (di dalam container app)
     * @param string $outputCsv Path absolut ke CSV output (di dalam container app)
     * @param int    $timeoutSeconds
     *
     * @return array{output:string, exit_code:int, timed_out:bool}
     */
    public function run(string $inputCsv, string $outputCsv, int $timeoutSeconds): array;
}
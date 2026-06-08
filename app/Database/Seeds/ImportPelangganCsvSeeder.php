<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ImportPelangganCsvSeeder extends Seeder
{
    public function run()
    {
        $file = WRITEPATH . 'uploads/DATA PELANGGAN 1.csv';

        if (! file_exists($file)) {
            echo "File tidak ditemukan: $file\n";
            return;
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            echo "Gagal membuka file.\n";
            return;
        }

        $header = fgets($handle);

        $batch = [];
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = str_getcsv($line, ';');

            $nama_pelanggan = $row[1] ?? '';
            $profesi        = ($row[2] ?? '') !== '' ? $row[2] : null;
            $agama          = ($row[3] ?? '') !== '' ? $row[3] : null;
            $dob_raw        = $row[4] ?? '';
            $telepon        = $row[5] ?? '';
            $email          = ($row[6] ?? '') !== '' ? $row[6] : null;

            $tanggal_lahir = null;
            if ($dob_raw !== '') {
                $dt = \DateTime::createFromFormat('j-M-Y', $dob_raw);
                if ($dt !== false) {
                    $tanggal_lahir = $dt->format('Y-m-d');
                }
            }

            $batch[] = [
                'nama_pelanggan' => $nama_pelanggan,
                'email'          => $email,
                'telepon'        => $telepon,
                'alamat'         => null,
                'agama'          => $agama,
                'tanggal_lahir'  => $tanggal_lahir,
                'profesi'        => $profesi,
                'segment'        => null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            $count++;

            if (count($batch) >= 100) {
                $this->db->table('tb_pelanggan')->insertBatch($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->db->table('tb_pelanggan')->insertBatch($batch);
        }

        fclose($handle);

        echo "Import selesai: $count baris ditambahkan ke tb_pelanggan.\n";
    }
}

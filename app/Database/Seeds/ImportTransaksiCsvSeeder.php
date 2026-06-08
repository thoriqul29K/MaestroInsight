<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ImportTransaksiCsvSeeder extends Seeder
{
    public function run()
    {
        $file = WRITEPATH . 'uploads/DATA TRANSAKSI PELANGGAN.csv';

        if (! file_exists($file)) {
            echo "File tidak ditemukan: $file\n";
            return;
        }

        $pelangganMap = $this->db->table('tb_pelanggan')
            ->select('id, nama_pelanggan')
            ->get()
            ->getResultArray();

        $namaKeId = [];
        foreach ($pelangganMap as $p) {
            $namaKeId[$p['nama_pelanggan']] = (int) $p['id'];
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            echo "Gagal membuka file.\n";
            return;
        }

        fgets($handle);

        $batch = [];
        $count = 0;
        $skipped = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = str_getcsv($line, '|');

            $nama        = trim($row[1] ?? '');
            $tanggalRaw  = trim($row[2] ?? '');
            $jumlahRaw   = trim($row[3] ?? '');

            if (! isset($namaKeId[$nama])) {
                $skipped[] = $nama;
                continue;
            }

            $idPelanggan = $namaKeId[$nama];

            $tanggalTransaksi = null;
            if ($tanggalRaw !== '') {
                $dt = \DateTime::createFromFormat('d/m/Y', $tanggalRaw);
                if ($dt !== false) {
                    $tanggalTransaksi = $dt->format('Y-m-d');
                }
            }

            $jumlahTransaksi = (int) str_replace('.', '', $jumlahRaw);

            $batch[] = [
                'id_pelanggan'      => $idPelanggan,
                'tanggal_transaksi' => $tanggalTransaksi,
                'layanan'           => null,
                'tujuan'            => null,
                'jumlah_transaksi'  => $jumlahTransaksi,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $count++;

            if (count($batch) >= 100) {
                $this->db->table('tb_transaksi')->insertBatch($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->db->table('tb_transaksi')->insertBatch($batch);
        }

        fclose($handle);

        echo "Import selesai: $count baris ditambahkan ke tb_transaksi.\n";
        if (! empty($skipped)) {
            echo "Nama tidak ditemukan di tb_pelanggan (" . count($skipped) . "): " . implode(', ', array_unique($skipped)) . "\n";
        }
    }
}

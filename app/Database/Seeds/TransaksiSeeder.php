<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run()
    {
        $transaksi = [];
        $today = date('Y-m-d');

        $pelanggan = [
            1 => ['count' => 8,  'total' => 48000000],  // Loyal
            2 => ['count' => 5,  'total' => 22000000],  // Potential
            3 => ['count' => 2,  'total' => 6500000],   // Budget Hunter
            4 => ['count' => 6,  'total' => 30000000],  // Potential
            5 => ['count' => 1,  'total' => 2000000],   // At Risk
            6 => ['count' => 3,  'total' => 18000000],  // Seasonal
            7 => ['count' => 7,  'total' => 42000000],  // Loyal
            8 => ['count' => 4,  'total' => 15000000],  // Seasonal
            9 => ['count' => 1,  'total' => 3500000],   // At Risk
            10 => ['count' => 5, 'total' => 25000000],  // Potential
        ];

        foreach ($pelanggan as $idPelanggan => $info) {
            $baseDate = strtotime('-' . (90 + ($idPelanggan * 5)) . ' days');
            for ($i = 0; $i < $info['count']; $i++) {
                $daysOffset = intdiv(90, max(1, $info['count']));
                $tanggal = date('Y-m-d', strtotime("+{$i} days", $baseDate));
                $transaksi[] = [
                    'id_pelanggan'     => $idPelanggan,
                    'tanggal_transaksi' => $tanggal,
                    'layanan'          => 'Paket Wisata',
                    'tujuan'           => $this->getTujuan($idPelanggan),
                    'jumlah_transaksi' => intdiv($info['total'], $info['count']),
                    'created_at'       => $today . ' 00:00:00',
                    'updated_at'       => $today . ' 00:00:00',
                ];
            }
        }

        $this->db->table('tb_transaksi')->insertBatch($transaksi);
    }

    private function getTujuan(int $id): string
    {
        $tujuan = [
            'Bali', 'Yogyakarta', 'Lombok', 'Bandung', 'Malang',
            'Surabaya', 'Bromo', 'Raja Ampat', 'Belitung', 'Singapore',
        ];
        return $tujuan[($id - 1) % count($tujuan)];
    }
}

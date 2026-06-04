<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama_pelanggan' => 'Budi Santoso',    'email' => 'budi.santoso@email.com',    'telepon' => '081234567001', 'alamat' => 'Jl. Sudirman No. 1, Jakarta',  'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Siti Aminah',     'email' => 'siti.aminah@email.com',     'telepon' => '081234567002', 'alamat' => 'Jl. Diponegoro No. 5, Bandung', 'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Andi Wijaya',     'email' => 'andi.wijaya@email.com',     'telepon' => '081234567003', 'alamat' => 'Jl. Gatot Subroto, Surabaya',   'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Dewi Lestari',    'email' => 'dewi.lestari@email.com',    'telepon' => '081234567004', 'alamat' => 'Jl. Asia Afrika, Bandung',      'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Rudi Hermawan',   'email' => 'rudi.hermawan@email.com',   'telepon' => '081234567005', 'alamat' => 'Jl. Malioboro, Yogyakarta',     'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Lina Marlina',    'email' => 'lina.marlina@email.com',    'telepon' => '081234567006', 'alamat' => 'Jl. Pahlawan, Semarang',        'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Hendra Gunawan',  'email' => 'hendra.gunawan@email.com',  'telepon' => '081234567007', 'alamat' => 'Jl. Tunjungan, Surabaya',       'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Maya Sari',       'email' => 'maya.sari@email.com',       'telepon' => '081234567008', 'alamat' => 'Jl. Legian, Bali',              'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Fajar Nugroho',   'email' => 'fajar.nugroho@email.com',   'telepon' => '081234567009', 'alamat' => 'Jl. Veteran, Solo',             'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nama_pelanggan' => 'Indah Permata',   'email' => 'indah.permata@email.com',   'telepon' => '081234567010', 'alamat' => 'Jl. Pemuda, Medan',             'segment' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];

        $this->db->table('tb_pelanggan')->insertBatch($data);
    }
}

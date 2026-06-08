<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgamaTanggalLahirToPelanggan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_pelanggan', [
            'agama' => [
                'type'       => 'ENUM',
                'constraint' => ['Islam', 'Kristen', 'Katolik', 'Buddha', 'Hindu', 'Lainnya'],
                'null'       => true,
                'after'      => 'alamat',
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'agama',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_pelanggan', 'agama');
        $this->forge->dropColumn('tb_pelanggan', 'tanggal_lahir');
    }
}

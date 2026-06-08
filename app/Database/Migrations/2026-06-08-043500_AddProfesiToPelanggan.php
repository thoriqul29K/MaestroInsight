<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfesiToPelanggan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_pelanggan', [
            'profesi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tanggal_lahir',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_pelanggan', 'profesi');
    }
}

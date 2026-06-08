<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeEmailNullableInPelanggan extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tb_pelanggan', [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('tb_pelanggan', [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
        ]);
    }
}

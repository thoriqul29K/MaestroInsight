<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeLayananTujuanNullableInTransaksi extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tb_transaksi', [
            'layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('tb_transaksi', [
            'layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
        ]);
    }
}

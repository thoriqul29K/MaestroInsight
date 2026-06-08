<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetailTransaksiToTransaksi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_transaksi', [
            'detail_transaksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_transaksi', 'detail_transaksi');
    }
}
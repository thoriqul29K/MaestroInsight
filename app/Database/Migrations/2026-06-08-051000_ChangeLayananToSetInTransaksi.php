<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeLayananToSetInTransaksi extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tb_transaksi', [
            'layanan' => [
                'type'       => 'SET',
                'constraint' => ['Dokumen', 'Cruise', 'Tour', 'Hotel', 'Transport', 'Ticket'],
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
                'null'       => true,
            ],
        ]);
    }
}

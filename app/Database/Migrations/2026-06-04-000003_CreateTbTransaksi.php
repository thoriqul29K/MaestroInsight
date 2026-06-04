<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTbTransaksi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pelanggan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal_transaksi' => [
                'type' => 'DATE',
            ],
            'layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'jumlah_transaksi' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_pelanggan', 'tb_pelanggan', 'id', '', 'CASCADE');
        $this->forge->createTable('tb_transaksi');
    }

    public function down()
    {
        $this->forge->dropTable('tb_transaksi');
    }
}

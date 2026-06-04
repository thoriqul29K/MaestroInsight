<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTbRfm extends Migration
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
            'recency' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'frequency' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'monetary' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'recency_norm' => [
                'type'       => 'FLOAT',
                'constraint' => '10,6',
                'null'       => true,
            ],
            'frequency_norm' => [
                'type'       => 'FLOAT',
                'constraint' => '10,6',
                'null'       => true,
            ],
            'monetary_norm' => [
                'type'       => 'FLOAT',
                'constraint' => '10,6',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_pelanggan', 'tb_pelanggan', 'id', '', 'CASCADE');
        $this->forge->createTable('tb_rfm');
    }

    public function down()
    {
        $this->forge->dropTable('tb_rfm');
    }
}

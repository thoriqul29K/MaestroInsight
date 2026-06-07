<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromosiLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_pelanggan'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nama_pelanggan' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'email_target'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'channel'        => ['type' => 'ENUM', 'constraint' => ['email', 'whatsapp', 'telegram', 'sms'], 'default' => 'email'],
            'subject'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['success', 'failed'], 'default' => 'failed'],
            'error_message'  => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('id_pelanggan', 'tb_pelanggan', 'id', '', 'SET NULL');
        $this->forge->createTable('tb_promosi_log');
    }

    public function down()
    {
        $this->forge->dropTable('tb_promosi_log', true);
    }
}

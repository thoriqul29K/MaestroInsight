<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTbPelanggan extends Migration
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
            'nama_pelanggan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'segment' => [
                'type'       => 'ENUM',
                'constraint' => ['loyal', 'potential', 'budget', 'seasonal', 'at_risk'],
                'null'       => true,
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
        $this->forge->createTable('tb_pelanggan');
    }

    public function down()
    {
        $this->forge->dropTable('tb_pelanggan');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToTbUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_user', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'superadmin'],
                'default'    => 'admin',
                'after'      => 'nama_lengkap',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_user', 'role');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropRoleFromTbUser extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('tb_user', 'role');
    }

    public function down()
    {
        $this->forge->addColumn('tb_user', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin'],
                'default'    => 'admin',
            ],
        ]);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('tb_user');

        $admin = $builder->where('username', 'admin')->get()->getRowArray();
        if (! $admin) {
            $builder->insert([
                'username'     => 'admin',
                'password'     => password_hash('admin123', PASSWORD_BCRYPT),
                'nama_lengkap' => 'Administrator',
                'role'         => 'admin',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $superadmin = $builder->where('username', 'superadmin')->get()->getRowArray();
        if (! $superadmin) {
            $builder->insert([
                'username'     => 'superadmin',
                'password'     => password_hash('admin123', PASSWORD_BCRYPT),
                'nama_lengkap' => 'Super Administrator',
                'role'         => 'superadmin',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }
}

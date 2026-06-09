<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GkjSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin user
        $this->db->table('users')->insert([
            'nama'       => 'Administrator',
            'username'   => 'admin',
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'role'       => 'admin',
            'status'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Komisi default
        $komisi = [
            ['nama_komisi' => 'Komisi Anak',        'ketua' => '-', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_komisi' => 'Komisi Pemuda',       'ketua' => '-', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_komisi' => 'Komisi Wanita',       'ketua' => '-', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_komisi' => 'Komisi Pria',         'ketua' => '-', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_komisi' => 'Komisi Lansia',       'ketua' => '-', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_komisi' => 'Komisi Diakonia',     'ketua' => '-', 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('komisi')->insertBatch($komisi);
    }
}

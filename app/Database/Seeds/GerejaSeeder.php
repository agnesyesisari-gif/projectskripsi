<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GerejaSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('komisi')->insertBatch([
            ['nama' => 'Warta', 'deskripsi' => 'Komisi publikasi dan media'],
            ['nama' => 'Pelayanan', 'deskripsi' => 'Komisi pelayanan dan liturgi'],
            ['nama' => 'Pemuda', 'deskripsi' => 'Komisi kegiatan pemuda'],
        ]);

        $this->db->table('jadwal_ibadah')->insertBatch([
            ['nama_ibadah' => 'Ibadah Minggu', 'tanggal' => date('Y-m-d', strtotime('+3 days')), 'waktu_mulai' => '08:00:00', 'waktu_selesai' => '10:00:00', 'lokasi' => 'Gereja Utama', 'keterangan' => 'Ibadah jemaat umum'],
            ['nama_ibadah' => 'Ibadah Rabu', 'tanggal' => date('Y-m-d', strtotime('+6 days')), 'waktu_mulai' => '19:00:00', 'waktu_selesai' => '20:30:00', 'lokasi' => 'Gereja Utama', 'keterangan' => 'Ibadah doa dan pujian'],
        ]);

        $this->db->table('program_kerja')->insertBatch([
            ['komisi_id' => 1, 'nama_program' => 'Buat buletin bulanan', 'deskripsi' => 'Menerbitkan buletin kegiatan gereja', 'tanggal_mulai' => date('Y-m-d'), 'tanggal_selesai' => date('Y-m-d', strtotime('+30 days')), 'status' => 'proses'],
            ['komisi_id' => 2, 'nama_program' => 'Pembinaan team liturgi', 'deskripsi' => 'Latihan paduan suara dan tata ibadah', 'tanggal_mulai' => date('Y-m-d'), 'tanggal_selesai' => date('Y-m-d', strtotime('+45 days')), 'status' => 'perencanaan'],
        ]);
    }
}

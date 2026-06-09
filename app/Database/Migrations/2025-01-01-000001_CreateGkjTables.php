<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGkjTables extends Migration
{
    public function up(): void
    {
        // ── users ──────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'       => ['type' => 'ENUM', 'constraint' => ['admin','petugas'], 'default' => 'petugas'],
            'status'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');

        // ── komisi ─────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_komisi' => ['type' => 'VARCHAR', 'constraint' => 100],
            'ketua'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'deskripsi'   => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('komisi');

        // ── jadwal_ibadah ──────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_ibadah' => ['type' => 'VARCHAR', 'constraint' => 150],
            'tanggal'     => ['type' => 'DATE'],
            'jam'         => ['type' => 'TIME'],
            'lokasi'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'petugas'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'komisi_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'keterangan'  => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('jadwal_ibadah');

        // ── program_kerja ──────────────────────────────
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_program' => ['type' => 'VARCHAR', 'constraint' => 200],
            'komisi_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'bulan'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'tahun'        => ['type' => 'SMALLINT', 'unsigned' => true],
            'anggaran'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['rencana','berjalan','selesai','batal'], 'default' => 'rencana'],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('program_kerja');

        // ── pengumuman ─────────────────────────────────
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'judul'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'isi'            => ['type' => 'TEXT'],
            'tanggal_tayang' => ['type' => 'DATE', 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['draft','aktif','nonaktif'], 'default' => 'draft'],
            'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pengumuman');

        // ── wa_log ─────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nomor'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'pesan'      => ['type' => 'TEXT'],
            'status'     => ['type' => 'ENUM', 'constraint' => ['terkirim','gagal'], 'default' => 'terkirim'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('wa_log');
    }

    public function down(): void
    {
        $this->forge->dropTable('wa_log',       true);
        $this->forge->dropTable('pengumuman',   true);
        $this->forge->dropTable('program_kerja',true);
        $this->forge->dropTable('jadwal_ibadah',true);
        $this->forge->dropTable('komisi',       true);
        $this->forge->dropTable('users',        true);
    }
}

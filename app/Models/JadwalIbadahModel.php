<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalIbadahModel extends Model
{
    protected $table         = 'jadwal_ibadah';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama_ibadah','tanggal','jam','lokasi','petugas','komisi_id','keterangan'];
    protected $useTimestamps = true;

    public function getWithKomisi(int $bulan, int $tahun, string $search = ''): array
    {
        $builder = $this->db->table('jadwal_ibadah j')
            ->select('j.*, k.nama_komisi')
            ->join('komisi k', 'j.komisi_id = k.id', 'left')
            ->where('MONTH(j.tanggal)', $bulan)
            ->where('YEAR(j.tanggal)', $tahun);

        if ($search) {
            $builder->groupStart()
                ->like('j.nama_ibadah', $search)
                ->orLike('j.lokasi', $search)
                ->orLike('j.petugas', $search)
                ->groupEnd();
        }

        return $builder->orderBy('j.tanggal', 'ASC')
            ->orderBy('j.jam', 'ASC')
            ->get()->getResultArray();
    }

    public function countMingguIni(): int
    {
        return (int)$this->db->table('jadwal_ibadah')
            ->selectCount('id')
            ->where('WEEK(tanggal) = WEEK(NOW())')
            ->where('YEAR(tanggal) = YEAR(NOW())')
            ->get()->getRow()->id;
    }
}

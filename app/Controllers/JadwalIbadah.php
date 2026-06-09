<?php

namespace App\Controllers;

use App\Models\JadwalIbadahModel;
use App\Models\KomisiModel;

class JadwalIbadah extends BaseController
{
    private JadwalIbadahModel $model;
    private KomisiModel $komisiModel;

    public function __construct()
    {
        $this->model       = new JadwalIbadahModel();
        $this->komisiModel = new KomisiModel();
    }

    public function index(): string
    {
        $bulan  = (int)($this->request->getGet('bulan')  ?? date('n'));
        $tahun  = (int)($this->request->getGet('tahun')  ?? date('Y'));
        $search = trim($this->request->getGet('search')  ?? '');

        return $this->render('jadwal_ibadah/index', [
            'pageTitle'  => 'Jadwal Ibadah',
            'activePage' => 'jadwal-ibadah',
            'jadwals'    => $this->model->getWithKomisi($bulan, $tahun, $search),
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'search'     => $search,
        ]);
    }

    public function tambah(): string
    {
        return $this->render('jadwal_ibadah/form', [
            'pageTitle'  => 'Tambah Jadwal Ibadah',
            'activePage' => 'jadwal-tambah',
            'komisiList' => $this->komisiModel->orderBy('nama_komisi')->findAll(),
            'data'       => [],
            'isEdit'     => false,
        ]);
    }

    public function simpan()
    {
        $rules = [
            'nama_ibadah' => 'required|max_length[150]',
            'tanggal'     => 'required|valid_date',
            'jam'         => 'required',
            'lokasi'      => 'required|max_length[200]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'nama_ibadah' => $this->request->getPost('nama_ibadah'),
            'tanggal'     => $this->request->getPost('tanggal'),
            'jam'         => $this->request->getPost('jam'),
            'lokasi'      => $this->request->getPost('lokasi'),
            'petugas'     => $this->request->getPost('petugas'),
            'komisi_id'   => $this->request->getPost('komisi_id') ?: null,
            'keterangan'  => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('jadwal'))->with('success', 'Jadwal ibadah berhasil ditambahkan.');
    }

    public function edit(int $id): string
    {
        $data = $this->model->find($id);
        if (! $data) {
            return redirect()->to(base_url('jadwal'))->with('error', 'Data tidak ditemukan.')->send() ?: '';
        }

        return $this->render('jadwal_ibadah/form', [
            'pageTitle'  => 'Edit Jadwal Ibadah',
            'activePage' => 'jadwal-ibadah',
            'komisiList' => $this->komisiModel->orderBy('nama_komisi')->findAll(),
            'data'       => $data,
            'isEdit'     => true,
        ]);
    }

    public function update(int $id)
    {
        $rules = [
            'nama_ibadah' => 'required|max_length[150]',
            'tanggal'     => 'required|valid_date',
            'jam'         => 'required',
            'lokasi'      => 'required|max_length[200]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'nama_ibadah' => $this->request->getPost('nama_ibadah'),
            'tanggal'     => $this->request->getPost('tanggal'),
            'jam'         => $this->request->getPost('jam'),
            'lokasi'      => $this->request->getPost('lokasi'),
            'petugas'     => $this->request->getPost('petugas'),
            'komisi_id'   => $this->request->getPost('komisi_id') ?: null,
            'keterangan'  => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('jadwal'))->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        $this->model->delete($id);
        return redirect()->to(base_url('jadwal'))->with('success', 'Jadwal berhasil dihapus.');
    }
}

<?php

namespace App\Controllers;

use App\Models\KomisiModel;
use App\Models\ProgramKerjaModel;

class ProgramKerja extends BaseController
{
    private ProgramKerjaModel $model;
    private KomisiModel $komisiModel;

    public function __construct()
    {
        $this->model       = new ProgramKerjaModel();
        $this->komisiModel = new KomisiModel();
    }

    // ─────────────────── KOMISI ───────────────────────
    public function komisi(): string
    {
        return $this->render('program_kerja/komisi', [
            'pageTitle'   => 'Data Komisi',
            'activePage'  => 'komisi',
            'komisiList'  => $this->komisiModel->getAllWithCount(),
            'editData'    => null,
        ]);
    }

    public function komisiSimpan()
    {
        if (! $this->validate(['nama_komisi' => 'required|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->komisiModel->insert([
            'nama_komisi' => $this->request->getPost('nama_komisi'),
            'ketua'       => $this->request->getPost('ketua'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
        ]);
        return redirect()->to(base_url('komisi'))->with('success', 'Komisi berhasil ditambahkan.');
    }

    public function komisiEdit(int $id): string
    {
        return $this->render('program_kerja/komisi', [
            'pageTitle'  => 'Edit Komisi',
            'activePage' => 'komisi',
            'komisiList' => $this->komisiModel->getAllWithCount(),
            'editData'   => $this->komisiModel->find($id),
        ]);
    }

    public function komisiUpdate(int $id)
    {
        if (! $this->validate(['nama_komisi' => 'required|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->komisiModel->update($id, [
            'nama_komisi' => $this->request->getPost('nama_komisi'),
            'ketua'       => $this->request->getPost('ketua'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
        ]);
        return redirect()->to(base_url('komisi'))->with('success', 'Komisi berhasil diperbarui.');
    }

    public function komisiHapus(int $id)
    {
        $this->komisiModel->delete($id);
        return redirect()->to(base_url('komisi'))->with('success', 'Komisi berhasil dihapus.');
    }

    // ─────────────────── PROGRAM ──────────────────────
    public function index(): string
    {
        $filter = [
            'komisi_id' => $this->request->getGet('komisi_id'),
            'tahun'     => $this->request->getGet('tahun') ?? date('Y'),
            'status'    => $this->request->getGet('status'),
            'search'    => $this->request->getGet('search'),
        ];

        return $this->render('program_kerja/index', [
            'pageTitle'  => 'Program Kerja',
            'activePage' => 'program-kerja',
            'programs'   => $this->model->getWithKomisi($filter),
            'komisiList' => $this->komisiModel->orderBy('nama_komisi')->findAll(),
            'filter'     => $filter,
        ]);
    }

    public function tambah(): string
    {
        return $this->render('program_kerja/form', [
            'pageTitle'  => 'Tambah Program Kerja',
            'activePage' => 'program-tambah',
            'komisiList' => $this->komisiModel->orderBy('nama_komisi')->findAll(),
            'data'       => [],
            'isEdit'     => false,
        ]);
    }

    public function simpan()
    {
        $rules = [
            'nama_program' => 'required|max_length[200]',
            'komisi_id'    => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->insert([
            'nama_program' => $this->request->getPost('nama_program'),
            'komisi_id'    => $this->request->getPost('komisi_id'),
            'bulan'        => $this->request->getPost('bulan'),
            'tahun'        => $this->request->getPost('tahun'),
            'anggaran'     => $this->request->getPost('anggaran') ?: null,
            'status'       => $this->request->getPost('status'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ]);
        return redirect()->to(base_url('program'))->with('success', 'Program kerja berhasil ditambahkan.');
    }

    public function edit(int $id): string
    {
        return $this->render('program_kerja/form', [
            'pageTitle'  => 'Edit Program Kerja',
            'activePage' => 'program-kerja',
            'komisiList' => $this->komisiModel->orderBy('nama_komisi')->findAll(),
            'data'       => $this->model->find($id),
            'isEdit'     => true,
        ]);
    }

    public function update(int $id)
    {
        $rules = [
            'nama_program' => 'required|max_length[200]',
            'komisi_id'    => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->update($id, [
            'nama_program' => $this->request->getPost('nama_program'),
            'komisi_id'    => $this->request->getPost('komisi_id'),
            'bulan'        => $this->request->getPost('bulan'),
            'tahun'        => $this->request->getPost('tahun'),
            'anggaran'     => $this->request->getPost('anggaran') ?: null,
            'status'       => $this->request->getPost('status'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ]);
        return redirect()->to(base_url('program'))->with('success', 'Program kerja berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        $this->model->delete($id);
        return redirect()->to(base_url('program'))->with('success', 'Program kerja berhasil dihapus.');
    }
}

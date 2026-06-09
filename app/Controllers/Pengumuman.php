<?php

namespace App\Controllers;

use App\Models\PengumumanModel;

class Pengumuman extends BaseController
{
    private PengumumanModel $model;

    public function __construct()
    {
        $this->model = new PengumumanModel();
    }

    public function index(): string
    {
        return $this->render('pengumuman/index', [
            'pageTitle'  => 'Pengumuman',
            'activePage' => 'pengumuman',
            'list'       => $this->model->orderBy('created_at','DESC')->findAll(),
        ]);
    }

    public function tambah(): string
    {
        return $this->render('pengumuman/form', [
            'pageTitle'  => 'Tambah Pengumuman',
            'activePage' => 'pengumuman',
            'data'       => [],
            'isEdit'     => false,
        ]);
    }

    public function simpan()
    {
        $rules = ['judul' => 'required|max_length[200]', 'isi' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->insert([
            'judul'          => $this->request->getPost('judul'),
            'isi'            => $this->request->getPost('isi'),
            'tanggal_tayang' => $this->request->getPost('tanggal_tayang') ?: null,
            'status'         => $this->request->getPost('status'),
            'created_by'     => session()->get('user_id'),
        ]);
        return redirect()->to(base_url('pengumuman'))->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(int $id): string
    {
        return $this->render('pengumuman/form', [
            'pageTitle'  => 'Edit Pengumuman',
            'activePage' => 'pengumuman',
            'data'       => $this->model->find($id),
            'isEdit'     => true,
        ]);
    }

    public function update(int $id)
    {
        $rules = ['judul' => 'required|max_length[200]', 'isi' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->update($id, [
            'judul'          => $this->request->getPost('judul'),
            'isi'            => $this->request->getPost('isi'),
            'tanggal_tayang' => $this->request->getPost('tanggal_tayang') ?: null,
            'status'         => $this->request->getPost('status'),
        ]);
        return redirect()->to(base_url('pengumuman'))->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        $this->model->delete($id);
        return redirect()->to(base_url('pengumuman'))->with('success', 'Pengumuman berhasil dihapus.');
    }
}

<?php echo view('Layout/Header', ['title' => $title ?? 'Edit Komisi']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Komisi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('komisi/update/' . ($komisi['id_komisi'] ?? '')) ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Nama Komisi</label>
                            <input type="text" name="nama_komisi" class="form-control" value="<?= old('nama_komisi', $komisi['nama_komisi'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Komisi</label>
                            <input type="text" name="kode_komisi" class="form-control" value="<?= old('kode_komisi', $komisi['kode_komisi'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi', $komisi['deskripsi'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update</button>
                            <a href="<?= site_url('komisi') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

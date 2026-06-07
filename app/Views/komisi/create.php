<?php echo view('Layout/Header', ['title' => $title ?? 'Tambah Komisi']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Komisi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('komisi/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Nama Komisi</label>
                            <input type="text" name="nama_komisi" class="form-control" value="<?= old('nama_komisi') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Komisi</label>
                            <input type="text" name="kode_komisi" class="form-control" value="<?= old('kode_komisi') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                            <a href="<?= site_url('komisi') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

<?php echo view('Layout/Header', ['title' => $title ?? 'Tambah Pengeluaran']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Pengeluaran</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('keuangan/store-pengeluaran') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= old('tanggal', date('Y-m-d')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" required><?= old('keterangan') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control" value="<?= old('kategori') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" name="jumlah" class="form-control" value="<?= old('jumlah') ?>" required min="0">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i> Simpan</button>
                            <a href="<?= site_url('keuangan') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

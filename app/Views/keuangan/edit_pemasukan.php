<?php echo view('Layout/Header', ['title' => $title ?? 'Edit Pemasukan']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Pemasukan</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('keuangan/update/' . ($transaksi['id'] ?? '')) ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= old('tanggal', $transaksi['tanggal'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" required><?= old('keterangan', $transaksi['keterangan'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sumber Dana</label>
                            <input type="text" name="sumber" class="form-control" value="<?= old('sumber', $transaksi['sumber'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" name="jumlah" class="form-control" value="<?= old('jumlah', $transaksi['jumlah'] ?? '') ?>" required min="0">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update</button>
                            <a href="<?= site_url('keuangan') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

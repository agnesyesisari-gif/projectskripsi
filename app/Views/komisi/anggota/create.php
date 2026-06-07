<?php echo view('Layout/Header', ['title' => $title ?? 'Tambah Anggota Komisi']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tambah Anggota Komisi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('komisi/anggota/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_komisi" value="<?= $komisi['id_komisi'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Jemaat</label>
                            <select name="id_jemaat" class="form-select" required>
                                <option value="">-- Pilih Jemaat --</option>
                                <?php if (!empty($jemaat)): ?>
                                    <?php foreach ($jemaat as $j): ?>
                                        <option value="<?= $j['id'] ?>"><?= esc($j['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan dalam Komisi</label>
                            <input type="text" name="jabatan" class="form-control" value="<?= old('jabatan') ?>" placeholder="Ketua, Sekretaris, Anggota...">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                            <a href="<?= site_url('komisi/show/' . ($komisi['id_komisi'] ?? '')) ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

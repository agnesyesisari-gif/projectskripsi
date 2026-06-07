<?php echo view('Layout/Header', ['title' => $title ?? 'Tambah Program Kerja']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Program Kerja</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('kerja/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Nama Program</label>
                            <input type="text" name="nama_program" class="form-control" value="<?= old('nama_program') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Komisi</label>
                            <select name="id_komisi" class="form-select" required>
                                <option value="">-- Pilih Komisi --</option>
                                <?php if (!empty($komisi)): ?>
                                    <?php foreach ($komisi as $k): ?>
                                        <option value="<?= $k['id_komisi'] ?>" <?= old('id_komisi') == $k['id_komisi'] ? 'selected' : '' ?>>
                                            <?= esc($k['nama_komisi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="<?= old('tanggal_mulai') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" value="<?= old('tanggal_selesai') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Anggaran (Rp)</label>
                            <input type="number" name="anggaran" class="form-control" value="<?= old('anggaran') ?>" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                            <a href="<?= site_url('kerja') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

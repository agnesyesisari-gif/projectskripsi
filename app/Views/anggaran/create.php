<?php echo view('layout/Header', ['title' => $title ?? 'Tambah Anggaran']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Anggaran</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('anggaran/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Program Kerja</label>
                            <select name="program_id" class="form-select" required>
                                <option value="">-- Pilih Program --</option>
                                <?php foreach ($program_kerja ?? [] as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= old('program_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['nama_program'] ?? $p['nama_kegiatan'] ?? '-') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Anggaran</label>
                            <input type="text" name="nama_anggaran" class="form-control" value="<?= old('nama_anggaran') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah (Rp)</label>
                                <input type="number" name="jumlah" class="form-control" value="<?= old('jumlah') ?>" required min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Anggaran</label>
                                <input type="number" name="tahun_anggaran" class="form-control" value="<?= old('tahun_anggaran', date('Y')) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Periode</label>
                            <select name="periode" class="form-select" required>
                                <option value="tahunan">Tahunan</option>
                                <option value="semester">Semester</option>
                                <option value="triwulan">Triwulan</option>
                                <option value="bulanan">Bulanan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="rencana">Rencana</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="disetujui">Disetujui</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"><?= old('keterangan') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan</button>
                            <a href="<?= site_url('anggaran') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('layout/Footer'); ?>

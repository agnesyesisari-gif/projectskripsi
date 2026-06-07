<?php echo view('Layout/Header', ['title' => $title ?? 'Edit Petugas Ibadah']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Petugas Ibadah</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('jadwal-petugas-ibadah/update/' . ($petugas['id'] ?? '')) ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Jadwal Ibadah</label>
                            <select name="id_ibadah" class="form-select" required>
                                <option value="">-- Pilih Jadwal Ibadah --</option>
                                <?php if (!empty($ibadah)): ?>
                                    <?php foreach ($ibadah as $ib): ?>
                                        <option value="<?= $ib['id'] ?>" <?= ($petugas['id_ibadah'] ?? '') == $ib['id'] ? 'selected' : '' ?>>
                                            <?= esc($ib['jenis_ibadah'] . ' - ' . date('d M Y', strtotime($ib['tanggal']))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jemaat</label>
                            <select name="id_jemaat" class="form-select" required>
                                <option value="">-- Pilih Jemaat --</option>
                                <?php if (!empty($jemaat)): ?>
                                    <?php foreach ($jemaat as $j): ?>
                                        <option value="<?= $j['id'] ?>" <?= ($petugas['id_jemaat'] ?? '') == $j['id'] ? 'selected' : '' ?>>
                                            <?= esc($j['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Peran</label>
                            <select name="peran" class="form-select" required>
                                <?php $peranList = ['pemimpin_ibadah', 'pemandu_pujian', 'pemusik', 'penatua', 'diaken']; ?>
                                <?php foreach ($peranList as $p): ?>
                                    <option value="<?= $p ?>" <?= ($petugas['peran'] ?? '') === $p ? 'selected' : '' ?>>
                                        <?= ucwords(str_replace('_', ' ', $p)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?= ($petugas['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="konfirmasi" <?= ($petugas['status'] ?? '') === 'konfirmasi' ? 'selected' : '' ?>>Konfirmasi</option>
                                <option value="batal" <?= ($petugas['status'] ?? '') === 'batal' ? 'selected' : '' ?>>Batal</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update</button>
                            <a href="<?= site_url('jadwal-petugas-ibadah') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

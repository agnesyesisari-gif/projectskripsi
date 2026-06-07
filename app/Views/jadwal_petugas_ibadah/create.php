<?php echo view('Layout/Header', ['title' => $title ?? 'Tambah Petugas Ibadah']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Petugas Ibadah</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('jadwal-petugas-ibadah/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Jadwal Ibadah</label>
                            <select name="id_ibadah" class="form-select" required>
                                <option value="">-- Pilih Jadwal Ibadah --</option>
                                <?php if (!empty($ibadah)): ?>
                                    <?php foreach ($ibadah as $ib): ?>
                                        <option value="<?= $ib['id'] ?>"><?= esc($ib['jenis_ibadah'] . ' - ' . date('d M Y', strtotime($ib['tanggal']))) ?></option>
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
                                        <option value="<?= $j['id'] ?>"><?= esc($j['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Peran</label>
                            <select name="peran" class="form-select" required>
                                <option value="">-- Pilih Peran --</option>
                                <option value="pemimpin_ibadah">Pemimpin Ibadah</option>
                                <option value="pemandu_pujian">Pemandu Pujian</option>
                                <option value="pemusik">Pemusik</option>
                                <option value="penatua">Penatua</option>
                                <option value="diaken">Diaken</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                            <a href="<?= site_url('jadwal-petugas-ibadah') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

<?php echo view('layout/Header', ['title' => $title ?? 'Detail Anggaran']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Anggaran</h5>
                    <a href="<?= site_url('anggaran') ?>" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($anggaran)): ?>
                        <table class="table table-borderless">
                            <tr><th width="200">Kode Anggaran</th><td><code><?= esc($anggaran['kode_anggaran'] ?? '-') ?></code></td></tr>
                            <tr><th>Nama Anggaran</th><td><?= esc($anggaran['nama_anggaran'] ?? '-') ?></td></tr>
                            <tr><th>Program</th><td><?= esc($anggaran['nama_program'] ?? '-') ?></td></tr>
                            <tr><th>Jumlah</th><td>Rp <?= number_format($anggaran['jumlah'] ?? 0, 0, ',', '.') ?></td></tr>
                            <tr><th>Tahun</th><td><?= esc($anggaran['tahun_anggaran'] ?? '-') ?></td></tr>
                            <tr><th>Periode</th><td><?= ucfirst($anggaran['periode'] ?? '-') ?></td></tr>
                            <tr><th>Status</th><td><span class="badge bg-<?= $anggaran['status'] === 'disetujui' ? 'success' : ($anggaran['status'] === 'ditolak' ? 'danger' : 'warning') ?>"><?= ucfirst($anggaran['status'] ?? '-') ?></span></td></tr>
                            <tr><th>Keterangan</th><td><?= esc($anggaran['keterangan'] ?? '-') ?></td></tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Data tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('layout/Footer'); ?>

<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Pemasukan']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Pemasukan</h5>
                    <a href="<?= site_url('pemasukan') ?>" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pemasukan)): ?>
                        <table class="table table-borderless">
                            <tr><th width="200">Tanggal</th><td><?= date('d M Y', strtotime($pemasukan['tanggal'])) ?></td></tr>
                            <tr><th>Keterangan</th><td><?= esc($pemasukan['keterangan']) ?></td></tr>
                            <tr><th>Sumber Dana</th><td><?= esc($pemasukan['sumber'] ?? '-') ?></td></tr>
                            <tr><th>Jumlah</th><td>Rp <?= number_format($pemasukan['jumlah'], 0, ',', '.') ?></td></tr>
                            <tr><th>Status</th><td>
                                <span class="badge bg-<?= $pemasukan['status'] === 'disetujui' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($pemasukan['status'] ?? 'pending') ?>
                                </span>
                            </td></tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Data tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

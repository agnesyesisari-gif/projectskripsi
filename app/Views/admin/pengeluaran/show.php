<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Pengeluaran']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Pengeluaran</h5>
                    <a href="<?= site_url('pengeluaran') ?>" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pengeluaran)): ?>
                        <table class="table table-borderless">
                            <tr><th width="200">Tanggal</th><td><?= date('d M Y', strtotime($pengeluaran['tanggal'])) ?></td></tr>
                            <tr><th>Keterangan</th><td><?= esc($pengeluaran['keterangan']) ?></td></tr>
                            <tr><th>Kategori</th><td><?= esc($pengeluaran['kategori'] ?? '-') ?></td></tr>
                            <tr><th>Jumlah</th><td>Rp <?= number_format($pengeluaran['jumlah'], 0, ',', '.') ?></td></tr>
                            <tr><th>Status</th><td>
                                <span class="badge bg-<?= $pengeluaran['status'] === 'disetujui' ? 'success' : ($pengeluaran['status'] === 'ditolak' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($pengeluaran['status'] ?? 'pending') ?>
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

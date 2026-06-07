<?php echo view('Layout/Header', ['title' => $title ?? 'Transaksi Keuangan']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-exchange-alt text-primary me-2"></i>Transaksi Keuangan</h4>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th>Jumlah</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transaksi)): ?>
                            <?php foreach ($transaksi as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['jenis'] === 'pemasukan' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($item['jenis']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($item['keterangan']) ?></td>
                                    <td class="<?= $item['jenis'] === 'pemasukan' ? 'text-success' : 'text-danger' ?> fw-bold">
                                        Rp <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                    </td>
                                    <td><span class="badge bg-<?= $item['status'] === 'disetujui' ? 'success' : 'warning' ?>"><?= ucfirst($item['status'] ?? 'pending') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

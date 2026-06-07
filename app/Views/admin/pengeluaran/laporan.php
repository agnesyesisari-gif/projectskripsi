<?php echo view('Layout/Header', ['title' => $title ?? 'Laporan Pengeluaran']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-alt text-danger me-2"></i>Laporan Pengeluaran</h4>
        <a href="<?= site_url('pengeluaran') ?>" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?? date('Y-m-t') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th>Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        <?php if (!empty($pengeluaran)): ?>
                            <?php foreach ($pengeluaran as $i => $item): ?>
                                <?php $total += $item['jumlah']; ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
                                    <td><?= esc($item['keterangan']) ?></td>
                                    <td><?= esc($item['kategori'] ?? '-') ?></td>
                                    <td>Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-danger fw-bold">
                                <td colspan="4" class="text-end">Total</td>
                                <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

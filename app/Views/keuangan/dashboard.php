<?php echo view('Layout/Header', ['title' => $title ?? 'Dashboard Keuangan']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-wallet text-success me-2"></i>Dashboard Keuangan</h4>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-up fa-2x mb-2"></i>
                    <h5>Rp <?= number_format($total_pemasukan ?? 0, 0, ',', '.') ?></h5>
                    <p class="mb-0">Total Pemasukan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-down fa-2x mb-2"></i>
                    <h5>Rp <?= number_format($total_pengeluaran ?? 0, 0, ',', '.') ?></h5>
                    <p class="mb-0">Total Pengeluaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-balance-scale fa-2x mb-2"></i>
                    <h5>Rp <?= number_format(($total_pemasukan ?? 0) - ($total_pengeluaran ?? 0), 0, ',', '.') ?></h5>
                    <p class="mb-0">Saldo</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h5><?= $pending_approval ?? 0 ?></h5>
                    <p class="mb-0">Menunggu Approval</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-arrow-up text-success me-2"></i>Pemasukan Terbaru</span>
                    <a href="<?= site_url('pemasukan') ?>" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($pemasukan_terbaru)): ?>
                            <?php foreach ($pemasukan_terbaru as $item): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= esc($item['keterangan']) ?></span>
                                    <span class="text-success fw-bold">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center">Belum ada data.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-arrow-down text-danger me-2"></i>Pengeluaran Terbaru</span>
                    <a href="<?= site_url('pengeluaran') ?>" class="btn btn-sm btn-outline-danger">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($pengeluaran_terbaru)): ?>
                            <?php foreach ($pengeluaran_terbaru as $item): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= esc($item['keterangan']) ?></span>
                                    <span class="text-danger fw-bold">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center">Belum ada data.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

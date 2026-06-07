<?php echo view('layout/Header', ['title' => $title ?? 'Dashboard Anggaran']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-tachometer-alt text-success me-2"></i>Dashboard Anggaran <?= $tahun ?? date('Y') ?></h4>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <h5>Rp <?= number_format($total_anggaran_tahun_ini ?? 0, 0, ',', '.') ?></h5>
                    <p class="mb-0">Total Anggaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h5>Rp <?= number_format($anggaran_disetujui ?? 0, 0, ',', '.') ?></h5>
                    <p class="mb-0">Disetujui</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                    <h5>Rp <?= number_format($anggaran_realisasi ?? 0, 0, ',', '.') ?></h5>
                    <p class="mb-0">Realisasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-2x mb-2"></i>
                    <?php $pct = ($total_anggaran_tahun_ini ?? 0) > 0 ? round(($anggaran_realisasi / $total_anggaran_tahun_ini) * 100, 1) : 0; ?>
                    <h5><?= $pct ?>%</h5>
                    <p class="mb-0">% Realisasi</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><i class="fas fa-list me-2"></i>Anggaran per Program</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($anggaran_per_program)): ?>
                            <?php foreach ($anggaran_per_program as $item): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= esc($item['nama_program'] ?? '-') ?></span>
                                    <span class="fw-bold">Rp <?= number_format($item['total'] ?? 0, 0, ',', '.') ?></span>
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
                <div class="card-header"><i class="fas fa-calendar me-2"></i>Anggaran per Periode</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($anggaran_per_periode)): ?>
                            <?php foreach ($anggaran_per_periode as $item): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= ucfirst($item['periode'] ?? '-') ?></span>
                                    <span class="fw-bold">Rp <?= number_format($item['total'] ?? 0, 0, ',', '.') ?></span>
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
<?php echo view('layout/Footer'); ?>

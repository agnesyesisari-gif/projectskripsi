<?php echo view('Layout/Header', ['title' => $title ?? 'Dashboard Program Kerja']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard Program Kerja</h4>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h3><?= $total_program ?? 0 ?></h3>
                    <p class="mb-0">Total Program</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3><?= $program_selesai ?? 0 ?></h3>
                    <p class="mb-0">Selesai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-spinner fa-2x mb-2"></i>
                    <h3><?= $program_berjalan ?? 0 ?></h3>
                    <p class="mb-0">Berjalan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calendar fa-2x mb-2"></i>
                    <h3><?= $program_direncanakan ?? 0 ?></h3>
                    <p class="mb-0">Direncanakan</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>Program Terbaru</span>
            <a href="<?= site_url('kerja') ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($programs)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($programs as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($item['nama_program'] ?? '-') ?></strong>
                                <small class="d-block text-muted"><?= esc($item['nama_komisi'] ?? '-') ?></small>
                            </div>
                            <span class="badge bg-<?= $item['status'] === 'selesai' ? 'success' : ($item['status'] === 'berjalan' ? 'primary' : 'secondary') ?>">
                                <?= ucfirst($item['status'] ?? '-') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted text-center py-3">Belum ada program kerja.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

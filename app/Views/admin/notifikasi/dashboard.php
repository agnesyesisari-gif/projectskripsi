<?php echo view('Layout/Header', ['title' => $title ?? 'Dashboard Notifikasi']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-bell text-primary me-2"></i>Dashboard Notifikasi</h4>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-bell fa-2x mb-2"></i>
                    <h3><?= $total_notifikasi ?? 0 ?></h3>
                    <p class="mb-0">Total Notifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-envelope fa-2x mb-2"></i>
                    <h3><?= $belum_dibaca ?? 0 ?></h3>
                    <p class="mb-0">Belum Dibaca</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3><?= $sudah_dibaca ?? 0 ?></h3>
                    <p class="mb-0">Sudah Dibaca</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>Notifikasi Terbaru</span>
            <a href="<?= site_url('notifikasi/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Buat Notifikasi</a>
        </div>
        <div class="card-body">
            <?php if (!empty($notifikasi)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($notifikasi as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= esc($item['judul'] ?? 'Notifikasi') ?></strong>
                                <p class="mb-0 text-muted small"><?= esc($item['pesan'] ?? '') ?></p>
                            </div>
                            <small class="text-muted"><?= date('d M Y', strtotime($item['created_at'] ?? 'now')) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-center text-muted py-3">Belum ada notifikasi.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

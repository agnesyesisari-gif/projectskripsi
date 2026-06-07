<?php echo view('Layout/Header', ['title' => $title ?? 'Cari Log Aktivitas']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-search text-secondary me-2"></i>Cari Log Aktivitas</h4>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pengguna</label>
                    <input type="text" name="user" class="form-control" value="<?= esc($search['user'] ?? '') ?>" placeholder="Username...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Modul</label>
                    <input type="text" name="module" class="form-control" value="<?= esc($search['module'] ?? '') ?>" placeholder="Nama modul...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $search['start_date'] ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $search['end_date'] ?? date('Y-m-t') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Cari</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Pengguna</th><th>Aktivitas</th><th>Modul</th><th>IP Address</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $i => $log): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($log['username'] ?? $log['user_id'] ?? '-') ?></td>
                                    <td><?= esc($log['activity'] ?? $log['action'] ?? '-') ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($log['module'] ?? '-') ?></span></td>
                                    <td><code><?= esc($log['ip_address'] ?? '-') ?></code></td>
                                    <td><?= date('d M Y H:i', strtotime($log['created_at'] ?? 'now')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada hasil pencarian.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

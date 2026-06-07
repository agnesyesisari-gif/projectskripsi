<?php echo view('Layout/Header', ['title' => $title ?? 'Log Aktivitas']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-history text-secondary me-2"></i>Log Aktivitas</h4>
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
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada log aktivitas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

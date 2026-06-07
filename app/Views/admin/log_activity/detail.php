<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Log Aktivitas']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Detail Log Aktivitas</h5>
                    <a href="<?= site_url('log-activity') ?>" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($log)): ?>
                        <table class="table table-borderless">
                            <tr><th width="200">Pengguna</th><td><?= esc($log['username'] ?? $log['user_id'] ?? '-') ?></td></tr>
                            <tr><th>Aktivitas</th><td><?= esc($log['activity'] ?? $log['action'] ?? '-') ?></td></tr>
                            <tr><th>Modul</th><td><?= esc($log['module'] ?? '-') ?></td></tr>
                            <tr><th>IP Address</th><td><code><?= esc($log['ip_address'] ?? '-') ?></code></td></tr>
                            <tr><th>User Agent</th><td><small><?= esc($log['user_agent'] ?? '-') ?></small></td></tr>
                            <tr><th>Waktu</th><td><?= date('d M Y H:i:s', strtotime($log['created_at'] ?? 'now')) ?></td></tr>
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

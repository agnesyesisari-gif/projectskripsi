<?php echo view('Layout/Header', ['title' => $title ?? 'Riwayat Password']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-history text-secondary me-2"></i>Riwayat Perubahan Password</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Tanggal Perubahan</th><th>IP Address</th><th>Keterangan</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= date('d M Y H:i', strtotime($item['changed_at'] ?? $item['created_at'] ?? 'now')) ?></td>
                                    <td><code><?= esc($item['ip_address'] ?? '-') ?></code></td>
                                    <td><?= esc($item['keterangan'] ?? 'Password diubah') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat perubahan password.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

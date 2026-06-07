<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Notifikasi']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Detail Notifikasi</h5>
                    <a href="<?= site_url('notifikasi') ?>" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifikasi)): ?>
                        <table class="table table-borderless">
                            <tr><th width="200">Judul</th><td><?= esc($notifikasi['judul'] ?? '-') ?></td></tr>
                            <tr><th>Pesan</th><td><?= esc($notifikasi['pesan'] ?? '-') ?></td></tr>
                            <tr><th>Penerima</th><td><?= esc($notifikasi['penerima'] ?? 'Semua') ?></td></tr>
                            <tr><th>Status</th><td>
                                <span class="badge bg-<?= ($notifikasi['is_read'] ?? 0) ? 'secondary' : 'warning' ?>">
                                    <?= ($notifikasi['is_read'] ?? 0) ? 'Sudah Dibaca' : 'Belum Dibaca' ?>
                                </span>
                            </td></tr>
                            <tr><th>Tanggal</th><td><?= date('d M Y H:i', strtotime($notifikasi['created_at'] ?? 'now')) ?></td></tr>
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

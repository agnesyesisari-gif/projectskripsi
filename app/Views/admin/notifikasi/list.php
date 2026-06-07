<?php echo view('Layout/Header', ['title' => $title ?? 'Daftar Notifikasi']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-bell text-primary me-2"></i>Daftar Notifikasi</h4>
        <a href="<?= site_url('notifikasi/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Buat Notifikasi</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Judul</th><th>Pesan</th><th>Penerima</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($notifikasi)): ?>
                            <?php foreach ($notifikasi as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item['judul'] ?? '-') ?></td>
                                    <td><?= esc(substr($item['pesan'] ?? '', 0, 60)) ?>...</td>
                                    <td><?= esc($item['penerima'] ?? 'Semua') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($item['is_read'] ?? 0) ? 'secondary' : 'warning' ?>">
                                            <?= ($item['is_read'] ?? 0) ? 'Dibaca' : 'Belum Dibaca' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($item['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="<?= site_url('notifikasi/show/' . $item['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada notifikasi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

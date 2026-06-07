<?php echo view('Layout/Header', ['title' => $title ?? 'Kegiatan']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-calendar-alt text-primary me-2"></i><?= $title ?? 'Kegiatan' ?></h4>
        <a href="<?= site_url('kegiatan/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Nama Kegiatan</th><th>Tanggal</th><th>Tempat</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kegiatan)): ?>
                            <?php foreach ($kegiatan as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item['nama_kegiatan'] ?? '-') ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'] ?? 'now')) ?></td>
                                    <td><?= esc($item['tempat'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $item['status'] === 'selesai' ? 'success' : 'primary' ?>"><?= ucfirst($item['status'] ?? '-') ?></span></td>
                                    <td><a href="<?= site_url('kegiatan/detail/' . $item['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kegiatan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

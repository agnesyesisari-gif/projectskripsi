<?php echo view('Layout/Header', ['title' => $title ?? 'Program Kerja']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-briefcase text-primary me-2"></i><?= $title ?? 'Program Kerja' ?></h4>
        <a href="<?= site_url('kerja/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Nama Program</th><th>Komisi</th><th>Periode</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($programs)): ?>
                            <?php foreach ($programs as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item['nama_program'] ?? $item['nama_kegiatan'] ?? '-') ?></td>
                                    <td><?= esc($item['nama_komisi'] ?? '-') ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal_mulai'] ?? 'now')) ?></td>
                                    <td><span class="badge bg-<?= $item['status'] === 'selesai' ? 'success' : ($item['status'] === 'berjalan' ? 'primary' : 'secondary') ?>"><?= ucfirst($item['status'] ?? '-') ?></span></td>
                                    <td>
                                        <a href="<?= site_url('kerja/view/' . $item['id_program']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= site_url('kerja/edit/' . $item['id_program']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada program kerja.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

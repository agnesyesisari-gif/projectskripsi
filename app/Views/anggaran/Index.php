<?php echo view('layout/Header', ['title' => $title ?? 'Manajemen Anggaran']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-money-bill-wave text-success me-2"></i><?= $title ?? 'Manajemen Anggaran' ?></h4>
        <a href="<?= site_url('anggaran/create') ?>" class="btn btn-success"><i class="fas fa-plus me-1"></i> Tambah Anggaran</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr><th>#</th><th>Kode</th><th>Nama Anggaran</th><th>Program</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($anggaran)): ?>
                        <?php foreach ($anggaran as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><code><?= esc($item['kode_anggaran'] ?? '-') ?></code></td>
                                <td><?= esc($item['nama_anggaran'] ?? '-') ?></td>
                                <td><?= esc($item['nama_program'] ?? '-') ?></td>
                                <td>Rp <?= number_format($item['jumlah'] ?? 0, 0, ',', '.') ?></td>
                                <td><span class="badge bg-<?= $item['status'] === 'disetujui' ? 'success' : ($item['status'] === 'ditolak' ? 'danger' : 'warning') ?>"><?= ucfirst($item['status'] ?? '-') ?></span></td>
                                <td>
                                    <a href="<?= site_url('anggaran/show/' . $item['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="<?= site_url('anggaran/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data anggaran.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php echo view('layout/Footer'); ?>

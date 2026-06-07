<?php echo view('Layout/Header', ['title' => $title ?? 'Pengeluaran']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-arrow-circle-down text-danger me-2"></i><?= $title ?? 'Data Pengeluaran' ?></h4>
        <a href="<?= site_url('pengeluaran/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Pengeluaran
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pengeluaran)): ?>
                            <?php foreach ($pengeluaran as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
                                    <td><?= esc($item['keterangan']) ?></td>
                                    <td><?= esc($item['kategori'] ?? '-') ?></td>
                                    <td>Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['status'] === 'disetujui' ? 'success' : ($item['status'] === 'ditolak' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($item['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('pengeluaran/show/' . $item['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= site_url('pengeluaran/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pengeluaran.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

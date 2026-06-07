<?php echo view('Layout/Header', ['title' => $title ?? 'Pemasukan']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-arrow-circle-up text-success me-2"></i><?= $title ?? 'Data Pemasukan' ?></h4>
        <a href="<?= site_url('pemasukan/create') ?>" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Tambah Pemasukan
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Tanggal</th><th>Keterangan</th><th>Sumber</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pemasukan)): ?>
                            <?php foreach ($pemasukan as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
                                    <td><?= esc($item['keterangan']) ?></td>
                                    <td><?= esc($item['sumber'] ?? '-') ?></td>
                                    <td>Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['status'] === 'disetujui' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($item['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('pemasukan/show/' . $item['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= site_url('pemasukan/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pemasukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

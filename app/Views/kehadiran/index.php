<?php echo view('Layout/Header', ['title' => $title ?? 'Data Kehadiran']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-clipboard-check text-primary me-2"></i><?= $title ?? 'Data Kehadiran' ?></h4>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Nama Jemaat</th><th>Tanggal Ibadah</th><th>Jenis Ibadah</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kehadiran)): ?>
                            <?php foreach ($kehadiran as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item['nama_jemaat'] ?? $item['nama'] ?? '-') ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'] ?? 'now')) ?></td>
                                    <td><?= esc($item['jenis_ibadah'] ?? '-') ?></td>
                                    <td><span class="badge bg-success">Hadir</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data kehadiran.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

<?php echo view('Layout/Header', ['title' => $title ?? 'Program Kegiatan']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-list-alt text-primary me-2"></i>Program Kegiatan</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Nama Program</th><th>Komisi</th><th>Periode</th><th>Progress</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($programs)): ?>
                            <?php foreach ($programs as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item['nama_program'] ?? '-') ?></td>
                                    <td><?= esc($item['nama_komisi'] ?? '-') ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal_mulai'] ?? 'now')) ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: <?= $item['persentase_selesai'] ?? 0 ?>%">
                                                <?= $item['persentase_selesai'] ?? 0 ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada program.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

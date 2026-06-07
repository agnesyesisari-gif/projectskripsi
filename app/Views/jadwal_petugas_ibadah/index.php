<?php echo view('Layout/Header', ['title' => $title ?? 'Jadwal Petugas Ibadah']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-user-tie text-primary me-2"></i><?= $title ?? 'Jadwal Petugas Ibadah' ?></h4>
        <a href="<?= site_url('jadwal-petugas-ibadah/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Nama Jemaat</th><th>Peran</th><th>Tanggal Ibadah</th><th>Jenis Ibadah</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($petugas)): ?>
                            <?php foreach ($petugas as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item['nama_jemaat'] ?? $item['nama'] ?? '-') ?></td>
                                    <td><?= esc($item['peran'] ?? '-') ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal'] ?? 'now')) ?></td>
                                    <td><?= esc($item['jenis_ibadah'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $item['status'] === 'konfirmasi' ? 'success' : 'warning' ?>"><?= ucfirst($item['status'] ?? 'pending') ?></span></td>
                                    <td>
                                        <a href="<?= site_url('jadwal-petugas-ibadah/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada jadwal petugas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

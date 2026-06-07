<?php echo view('Layout/Header', ['title' => $title ?? 'Dashboard Komisi']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard Komisi</h4>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3><?= $total_komisi ?? 0 ?></h3>
                    <p class="mb-0">Total Komisi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-friends fa-2x mb-2"></i>
                    <h3><?= $total_anggota ?? 0 ?></h3>
                    <p class="mb-0">Total Anggota</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h3><?= $total_program ?? 0 ?></h3>
                    <p class="mb-0">Program Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                    <h3><?= $total_kegiatan ?? 0 ?></h3>
                    <p class="mb-0">Kegiatan Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header"><i class="fas fa-list me-2"></i>Daftar Komisi</div>
        <div class="card-body">
            <?php if (!empty($komisi)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light"><tr><th>Nama Komisi</th><th>Ketua</th><th>Anggota</th><th>Program</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($komisi as $item): ?>
                                <tr>
                                    <td><?= esc($item['nama_komisi']) ?></td>
                                    <td><?= esc($item['ketua'] ?? '-') ?></td>
                                    <td><?= $item['total_anggota'] ?? 0 ?></td>
                                    <td><?= $item['total_program'] ?? 0 ?></td>
                                    <td><a href="<?= site_url('komisi/show/' . $item['id_komisi']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-3">Belum ada data komisi.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

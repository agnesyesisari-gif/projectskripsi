<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Komisi']); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i><?= esc($komisi['nama_komisi'] ?? 'Detail Komisi') ?></h5>
            <a href="<?= site_url('komisi') ?>" class="btn btn-sm btn-light">Kembali</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Nama Komisi</th><td><?= esc($komisi['nama_komisi'] ?? '-') ?></td></tr>
                        <tr><th>Kode</th><td><?= esc($komisi['kode_komisi'] ?? '-') ?></td></tr>
                        <tr><th>Deskripsi</th><td><?= esc($komisi['deskripsi'] ?? '-') ?></td></tr>
                    </table>
                </div>
            </div>
            <hr>
            <h6><i class="fas fa-users me-2"></i>Anggota Komisi</h6>
            <?php if (!empty($anggota)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light"><tr><th>#</th><th>Nama</th><th>Jabatan</th><th>No. HP</th></tr></thead>
                        <tbody>
                            <?php foreach ($anggota as $i => $a): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($a['nama'] ?? '-') ?></td>
                                    <td><?= esc($a['jabatan'] ?? '-') ?></td>
                                    <td><?= esc($a['telepon'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada anggota.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

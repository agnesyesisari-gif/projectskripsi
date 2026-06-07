<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Kegiatan']); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?= esc($kegiatan['nama_kegiatan'] ?? 'Detail Kegiatan') ?></h5>
            <a href="<?= site_url('kegiatan') ?>" class="btn btn-sm btn-light">Kembali</a>
        </div>
        <div class="card-body">
            <?php if (!empty($kegiatan)): ?>
                <table class="table table-borderless">
                    <tr><th width="200">Nama Kegiatan</th><td><?= esc($kegiatan['nama_kegiatan'] ?? '-') ?></td></tr>
                    <tr><th>Tanggal</th><td><?= date('d M Y', strtotime($kegiatan['tanggal'] ?? 'now')) ?></td></tr>
                    <tr><th>Waktu</th><td><?= esc($kegiatan['waktu'] ?? '-') ?></td></tr>
                    <tr><th>Tempat</th><td><?= esc($kegiatan['tempat'] ?? '-') ?></td></tr>
                    <tr><th>Penanggung Jawab</th><td><?= esc($kegiatan['penanggung_jawab'] ?? '-') ?></td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge bg-<?= $kegiatan['status'] === 'selesai' ? 'success' : 'primary' ?>">
                            <?= ucfirst($kegiatan['status'] ?? '-') ?>
                        </span>
                    </td></tr>
                    <tr><th>Deskripsi</th><td><?= esc($kegiatan['deskripsi'] ?? '-') ?></td></tr>
                </table>
            <?php else: ?>
                <p class="text-muted">Data tidak ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

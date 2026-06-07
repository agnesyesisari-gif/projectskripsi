<?php echo view('Layout/Header', ['title' => $title ?? 'Detail Program Kerja']); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i><?= esc($program['nama_program'] ?? 'Detail Program') ?></h5>
            <a href="<?= site_url('kerja') ?>" class="btn btn-sm btn-light">Kembali</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Nama Program</th><td><?= esc($program['nama_program'] ?? '-') ?></td></tr>
                        <tr><th>Komisi</th><td><?= esc($program['nama_komisi'] ?? '-') ?></td></tr>
                        <tr><th>Tanggal Mulai</th><td><?= date('d M Y', strtotime($program['tanggal_mulai'] ?? 'now')) ?></td></tr>
                        <tr><th>Tanggal Selesai</th><td><?= date('d M Y', strtotime($program['tanggal_selesai'] ?? 'now')) ?></td></tr>
                        <tr><th>Anggaran</th><td>Rp <?= number_format($program['anggaran'] ?? 0, 0, ',', '.') ?></td></tr>
                        <tr><th>Status</th><td>
                            <span class="badge bg-<?= $program['status'] === 'selesai' ? 'success' : ($program['status'] === 'berjalan' ? 'primary' : 'secondary') ?>">
                                <?= ucfirst($program['status'] ?? '-') ?>
                            </span>
                        </td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Deskripsi</h6>
                    <p><?= esc($program['deskripsi'] ?? 'Tidak ada deskripsi.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

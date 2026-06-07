<?php echo view('layout/Header', ['title' => $title ?? 'Laporan Anggaran']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-alt text-success me-2"></i>Laporan Anggaran</h4>
        <a href="<?= site_url('anggaran/export-pdf?tahun=' . $tahun . '&periode=' . $periode) ?>" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> Export PDF</a>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="<?= $tahun ?? date('Y') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Periode</label>
                    <select name="periode" class="form-select">
                        <?php foreach (['tahunan', 'semester', 'triwulan', 'bulanan'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($periode ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach (['rencana', 'diajukan', 'disetujui', 'ditolak', 'realisasi'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr><th>#</th><th>Nama Anggaran</th><th>Program</th><th>Jumlah</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan)): ?>
                        <?php foreach ($laporan as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= esc($item['nama_anggaran'] ?? '-') ?></td>
                                <td><?= esc($item['nama_program'] ?? '-') ?></td>
                                <td>Rp <?= number_format($item['jumlah'] ?? 0, 0, ',', '.') ?></td>
                                <td><span class="badge bg-<?= $item['status'] === 'disetujui' ? 'success' : ($item['status'] === 'ditolak' ? 'danger' : 'warning') ?>"><?= ucfirst($item['status'] ?? '-') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-success fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td>Rp <?= number_format($total_anggaran ?? 0, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php echo view('layout/Footer'); ?>

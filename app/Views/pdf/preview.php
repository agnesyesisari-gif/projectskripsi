<?php echo view('Layout/Header', ['title' => $title ?? 'Preview PDF']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-pdf text-danger me-2"></i>Preview Dokumen</h4>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-danger"><i class="fas fa-print me-1"></i> Cetak</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body" id="printArea">
            <div class="text-center mb-4">
                <h4><?= esc($church_info['nama_gereja'] ?? 'Sistem Informasi Gereja') ?></h4>
                <p class="text-muted"><?= esc($church_info['alamat'] ?? '') ?></p>
                <hr>
            </div>
            <?php if (!empty($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <p class="text-muted text-center">Tidak ada konten untuk ditampilkan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
@media print {
    nav, .btn, footer { display: none !important; }
    #printArea { padding: 0; }
}
</style>
<?php echo view('Layout/Footer'); ?>

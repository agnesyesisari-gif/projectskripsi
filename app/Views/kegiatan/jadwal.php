<?php echo view('Layout/Header', ['title' => $title ?? 'Jadwal Kegiatan']); ?>
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-calendar-week text-primary me-2"></i>Jadwal Kegiatan</h4>
    <div class="row">
        <?php if (!empty($jadwal)): ?>
            <?php foreach ($jadwal as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><?= esc($item['nama_kegiatan'] ?? '-') ?></h6>
                        </div>
                        <div class="card-body">
                            <p><i class="fas fa-calendar me-2"></i><?= date('d M Y', strtotime($item['tanggal'] ?? 'now')) ?></p>
                            <p><i class="fas fa-clock me-2"></i><?= $item['waktu'] ?? '-' ?></p>
                            <p><i class="fas fa-map-marker-alt me-2"></i><?= esc($item['tempat'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info text-center">Belum ada jadwal kegiatan.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

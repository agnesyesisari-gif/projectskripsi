<?php echo view('Layout/Header', ['title' => $title ?? 'Data Komisi']); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-users text-primary me-2"></i><?= $title ?? 'Data Komisi' ?></h4>
        <a href="<?= site_url('komisi/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Komisi</a>
    </div>
    <div class="row">
        <?php if (!empty($komisi)): ?>
            <?php foreach ($komisi as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($item['nama_komisi']) ?></h5>
                            <p class="text-muted small"><?= esc($item['deskripsi'] ?? '-') ?></p>
                            <p class="mb-1"><i class="fas fa-user me-1"></i> <?= esc($item['ketua'] ?? '-') ?></p>
                            <p class="mb-0"><i class="fas fa-users me-1"></i> <?= $item['total_anggota'] ?? 0 ?> anggota</p>
                        </div>
                        <div class="card-footer d-flex gap-2">
                            <a href="<?= site_url('komisi/show/' . $item['id_komisi']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="<?= site_url('komisi/edit/' . $item['id_komisi']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info text-center">Belum ada data komisi.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

<?= $this->extend('templates/main_template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 text-primary"><i class="fas fa-eye me-2"></i>Detail Program</h1>
        <p class="text-muted">Informasi lengkap program kerja</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= site_url('program') ?>" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <a href="<?= site_url('program/edit/' . $program['id']) ?>" class="btn btn-warning me-2">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="<?= site_url('program/cetak/' . $program['id']) ?>" class="btn btn-info" target="_blank">
            <i class="fas fa-print me-1"></i>Cetak
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Informasi Utama -->
        <div class="card card-shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0 text-primary"><?= $program['nama_program'] ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Kategori:</strong>
                        <span class="badge bg-info ms-2"><?= $program['kategori'] ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <?php
                        $status_class = [
                            'Perencanaan' => 'bg-secondary',
                            'Berjalan' => 'bg-success',
                            'Selesai' => 'bg-primary',
                            'Ditunda' => 'bg-warning'
                        ];
                        ?>
                        <span class="badge <?= $status_class[$program['status']] ?> ms-2">
                            <?= $program['status'] ?>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Deskripsi Program:</strong>
                    <p class="mt-2"><?= nl2br($program['deskripsi']) ?></p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar-start me-2"></i>Tanggal Mulai:</strong>
                        <p class="mt-1"><?= date('d F Y', strtotime($program['tanggal_mulai'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar-check me-2"></i>Tanggal Selesai:</strong>
                        <p class="mt-1"><?= date('d F Y', strtotime($program['tanggal_selesai'])) ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-user me-2"></i>Penanggung Jawab:</strong>
                        <p class="mt-1"><?= $program['penanggung_jawab'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-money-bill me-2"></i>Anggaran:</strong>
                        <p class="mt-1">Rp <?= number_format($program['anggaran'] ?? 0, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

    <div class="col-lg-4">
        <!-- Informasi Tambahan -->
        <div class="card card-shadow mb-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Dibuat Pada:</strong>
                    <p class="mt-1"><?= date('d F Y H:i', strtotime($program['created_at'])) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Diupdate Pada:</strong>
                    <p class="mt-1"><?= date('d F Y H:i', strtotime($program['updated_at'])) ?></p>
                </div>
                <?php if($program['lampiran']): ?>
                <div class="mb-3">
                    <strong>Lampiran:</strong>
                    <div class="mt-2">
                        <a href="<?=
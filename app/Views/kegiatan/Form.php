<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><?= $title ?></h4>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= isset($kegiatan) ? site_url('kegiatan/update/' . $kegiatan['id_kegiatan']) : site_url('kegiatan/store') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <div class="row">
                                <!-- Nama Kegiatan -->
                                <div class="col-md-8 mb-3">
                                    <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= validation_show_error('nama_kegiatan') ? 'is-invalid' : '' ?>" 
                                           id="nama_kegiatan" name="nama_kegiatan" 
                                           value="<?= old('nama_kegiatan', $kegiatan['nama_kegiatan'] ?? '') ?>" required>
                                    <?php if (validation_show_error('nama_kegiatan')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('nama_kegiatan') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Jenis Kegiatan -->
                                <div class="col-md-4 mb-3">
                                    <label for="jenis_kegiatan" class="form-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                                    <select class="form-control <?= validation_show_error('jenis_kegiatan') ? 'is-invalid' : '' ?>" 
                                            id="jenis_kegiatan" name="jenis_kegiatan" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="Ibadah" <?= old('jenis_kegiatan', $kegiatan['jenis_kegiatan'] ?? '') == 'Ibadah' ? 'selected' : '' ?>>Ibadah</option>
                                        <option value="Program Kerja" <?= old('jenis_kegiatan', $kegiatan['jenis_kegiatan'] ?? '') == 'Program Kerja' ? 'selected' : '' ?>>Program Kerja</option>
                                        <option value="Lainnya" <?= old('jenis_kegiatan', $kegiatan['jenis_kegiatan'] ?? '') == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                    </select>
                                    <?php if (validation_show_error('jenis_kegiatan')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('jenis_kegiatan') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Kategori -->
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control <?= validation_show_error('kategori') ? 'is-invalid' : '' ?>" 
                                            id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Ibadah Minggu" <?= old('kategori', $kegiatan['kategori'] ?? '') == 'Ibadah Minggu' ? 'selected'
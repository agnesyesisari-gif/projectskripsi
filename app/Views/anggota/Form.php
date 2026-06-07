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
            <div class="col-md-10">
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

                        <form method="post" action="<?= isset($anggota) ? site_url('anggota/update/' . $anggota['id_anggota']) : site_url('anggota/store') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <div class="row">
                                <!-- Foto -->
                                <div class="col-md-3 mb-3">
                                    <label for="foto" class="form-label">Foto Profil</label>
                                    <div class="text-center">
                                        <?php if (isset($anggota) && $anggota['foto']): ?>
                                            <img src="<?= base_url('uploads/anggota/' . $anggota['foto']) ?>" 
                                                 id="previewFoto" 
                                                 class="img-thumbnail mb-2" 
                                                 style="max-width: 200px; max-height: 200px;"
                                                 onerror="this.src='<?= base_url('uploads/anggota/default.png') ?>'">
                                        <?php else: ?>
                                            <img src="<?= base_url('uploads/anggota/default.png') ?>" 
                                                 id="previewFoto" 
                                                 class="img-thumbnail mb-2" 
                                                 style="max-width: 200px; max-height: 200px;">
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <!-- Nama  -->
                                        <div class="col-md-6 mb-3">
                                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?= validation_show_error('nama') ? 'is-invalid' : '' ?>" 
                                                   id="nama" name="nama" 
                                                   value="<?= old('nama', $anggota['nama'] ?? '') ?>" required>
                                            <?php if (validation_show_error('nama')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('nama') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Tempat Lahir -->
                                        <div class="col-md-6 mb-3">
                                            <label for="tempat_lahir" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?= validation_show_error('tempat_lahir') ? 'is-invalid' : '' ?>" 
                                                   id="tempat_lahir" name="tempat_lahir" 
                                                   value="<?= old('tempat_lahir', $anggota['tempat_lahir'] ?? '') ?>" required>
                                            <?php if (validation_show_error('tempat_lahir')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('tempat_lahir') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Tanggal Lahir -->
                                        <div class="col-md-6 mb-3">
                                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control <?= validation_show_error('tanggal_lahir') ? 'is-invalid' : '' ?>" 
                                                   id="tanggal_lahir" name="tanggal_lahir" 
                                                   value="<?= old('tanggal_lahir', $anggota['tanggal_lahir'] ?? '') ?>" required>
                                            <?php if (validation_show_error('tanggal_lahir')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('tanggal_lahir') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Jenis Kelamin -->
                                        <div class="col-md-6 mb-3">
                                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                            <select class="form-control <?= validation_show_error('jenis_kelamin') ? 'is-invalid' : '' ?>" 
                                                    id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="">Pilih Jenis Kelamin</option>
                                                <option value="L" <?= old('jenis_kelamin', $anggota['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= old('jenis_kelamin', $anggota['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                            <?php if (validation_show_error('jenis_kelamin')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('jenis_kelamin') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Status Anggota -->
                                        <div class="col-md-6 mb-3">
                                            <label for="status_anggota" class="form-label">Status Anggota <span class="text-danger">*</span></label>
                                            <select class="form-control <?= validation_show_error('status_anggota') ? 'is-invalid' : '' ?>" 
                                                    id="status_anggota" name="status_anggota" required>
                                                <option value="">Pilih Status</option>
                                                <option value="Jemaat" <?= old('status_anggota', $anggota['status_anggota'] ?? '') == 'Jemaat' ? 'selected' : '' ?>>Jemaat</option>
                                                <option value="Aktivis" <?= old('status_anggota', $anggota['status_anggota'] ?? '') == 'Aktivis' ? 'selected' : '' ?>>Aktivis</option>
                                                <option value="Pelayan" <?= old('status_anggota', $anggota['status_anggota'] ?? '') == 'Pelayan' ? 'selected' : '' ?>>Pelayan</option>
                                                <option value="Majelis" <?= old('status_anggota', $anggota['status_anggota'] ?? '') == 'Majelis' ? 'selected' : '' ?>>Majelis</option>
                                                <option value="Pendeta" <?= old('status_anggota', $anggota['status_anggota'] ?? '') == 'Pendeta' ? 'selected' : '' ?>>Pendeta</option>
                                            </select>
                                            <?php if (validation_show_error('status_anggota')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('status_anggota') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Alamat -->
                                <div class="col-12 mb-3">
                                    <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control <?= validation_show_error('alamat') ? 'is-invalid' : '' ?>" 
                                              id="alamat" name="alamat" rows="3" required><?= old('alamat', $anggota['alamat'] ?? '') ?></textarea>
                                    <?php if (validation_show_error('alamat')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('alamat') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- No. Telepon -->
                                <div class="col-md-6 mb-3">
                                    <label for="no_telepon" class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control <?= validation_show_error('no_telepon') ? 'is-invalid' : '' ?>" 
                                           id="no_telepon" name="no_telepon" 
                                           value="<?= old('no_telepon', $anggota['no_telepon'] ?? '') ?>" required>
                                    <?php if (validation_show_error('no_telepon')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('no_telepon') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control <?= validation_show_error('email') ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" 
                                           value="<?= old('email', $anggota['email'] ?? '') ?>">
                                    <?php if (validation_show_error('email')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('email') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Tanggal Bergabung -->
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_bergabung" class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control <?= validation_show_error('tanggal_bergabung') ? 'is-invalid' : '' ?>" 
                                           id="tanggal_bergabung" name="tanggal_bergabung" 
                                           value="<?= old('tanggal_bergabung', $anggota['tanggal_bergabung'] ?? '') ?>" required>
                                    <?php if (validation_show_error('tanggal_bergabung')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('tanggal_bergabung') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Bidang Pelayanan -->
                                <div class="col-md-6 mb-3">
                                    <label for="bidang_pelayanan" class="form-label">Bidang Pelayanan</label>
                                    <input type="text" class="form-control <?= validation_show_error('bidang_pelayanan') ? 'is-invalid' : '' ?>" 
                                           id="bidang_pelayanan" name="bidang_pelayanan" 
                                           value="<?= old('bidang_pelayanan', $anggota['bidang_pelayanan'] ?? '') ?>">
                                    <?php if (validation_show_error('bidang_pelayanan')): ?>
                                        <div class="invalid-feedback">
                                            <?= validation_show_error('bidang_pelayanan') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan
                                    </button>
                                    <a href="<?= site_url('anggota') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Set max date for tanggal_lahir to today
        document.getElementById('tanggal_lahir').max = new Date().toISOString().split('T')[0];
        document.getElementById('tanggal_bergabung').max = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
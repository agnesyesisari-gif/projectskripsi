<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-img-large {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <div>
                            <a href="<?= site_url('anggota/edit/' . $anggota['id_anggota']) ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                            <a href="<?= site_url('anggota') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Foto Profil -->
                            <div class="col-md-4 text-center">
                                <img src="<?= base_url('uploads/anggota/' . $anggota['foto']) ?>" 
                                     alt="Foto <?= $anggota['nama_lengkap'] ?>" 
                                     class="profile-img-large mb-3"
                                     onerror="this.src='<?= base_url('uploads/anggota/default.png') ?>'">
                                <h4 class="text-muted">"<?= esc($anggota['nama']) ?>"</h5>
                                <span class="badge bg-<?= $anggota['status_aktif'] ? 'success' : 'secondary' ?> fs-6">
                                    <?= $anggota['status_aktif'] ? 'Aktif' : 'Tidak Aktif' ?>
                                </span>
                            </div>

                            <!-- Informasi Detail -->
                            <div class="col-md-8">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2">Informasi Pribadi</h5>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <span class="info-label">Tempat, Tanggal Lahir:</span><br>
                                            <?= esc($anggota['tempat_lahir']) ?>, <?= date('d F Y', strtotime($anggota['tanggal_lahir'])) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-
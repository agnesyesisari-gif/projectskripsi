<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Sistem Informasi Gereja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .program-card {
            border-left: 4px solid #667eea;
            transition: transform 0.3s;
        }
        .program-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark header-bg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-church me-2"></i>
                Sistem Informasi Gereja
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Flash Message -->
        <?php if ($this->session->flashdata('message')): ?>
            <?= $this->session->flashdata('message') ?>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tasks me-2"></i><?= $title ?></h2>
            <a href="<?= site_url('programkerja/tambah') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Tambah Program
            </a>
        </div>

        <!-- Program Kerja List -->
        <div class="row">
            <?php if (empty($program_kerja)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada program kerja yang ditambahkan.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($program_kerja as $program): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card program-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?= $program['nama_program'] ?></h5>
                                    <span class="badge <?= $program['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?> status-badge">
                                        <?= $program['status'] ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted small">
                                    <?= substr($program['deskripsi'], 0, 100) ?>...
                                </p>
                                
                                <div class="program-info mb-3">
                                    <div class="row small text-muted">
                                        <div class="col-12 mb-1">
                                            <i class="fas fa-user me-1"></i>
                                            <?= $program['penanggung_jawab'] ?>
                                        </div>
                                        <div class="col-12 mb-1">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($program['tanggal_mulai'])) ?> - 
                                            <?= date('d M Y', strtotime($program['tanggal_selesai'])) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="btn-group w-100">
                                    <a href="<?= site_url('programkerja/edit/' . $program['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('programkerja/hapus/' . $program['id']) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Yakin ingin menghapus program ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0 text-muted">
                &copy; <?= date('Y') ?> Sistem Informasi Gereja. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
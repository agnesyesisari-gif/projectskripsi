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
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark header-bg">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url('programkerja') ?>">
                <i class="fas fa-arrow-left me-2"></i>
                <?= $title ?>
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <!-- Flash Message -->
                    <?php if ($this->session->flashdata('message')): ?>
                        <?= $this->session->flashdata('message') ?>
                    <?php endif; ?>

                    <h4 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Tambah Program Kerja Baru</h4>

                    <?= form_open('programkerja/tambah') ?>
                        
                        <div class="mb-3">
                            <label for="nama_program" class="form-label">Nama Program *</label>
                            <input type="text" class="form-control <?= form_error('nama_program') ? 'is-invalid' : '' ?>" 
                                   id="nama_program" name="nama_program" 
                                   value="<?= set_value('nama_program') ?>" 
                                   placeholder="Masukkan nama program">
                            <?= form_error('nama_program', '<div class="invalid-feedback">', '</div>') ?>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Program *</label>
                            <textarea class="form-control <?= form_error('deskripsi') ? 'is-invalid' : '' ?>" 
                                      id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Jelaskan detail program kerja"><?= set_value('deskripsi') ?></textarea>
                            <?= form_error('deskripsi', '<div class="invalid-feedback">', '</div>') ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai *</label>
                                <input type="date" class="form-control <?= form_error('tanggal_mulai') ? 'is-invalid' : '' ?>" 
                                       id="tanggal_mulai" name="tanggal_mulai" 
                                       value="<?= set_value('tanggal_mulai') ?>">
                                <?= form_error('tanggal_mulai', '<div class="invalid-feedback">', '</div>') ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai *</label>
                                <input type="date" class="form-control <?= form_error('tanggal_selesai') ? 'is-invalid' : '' ?>" 
                                       id="tanggal_selesai" name="tanggal_selesai" 
                                       value="<?= set_value('tanggal_selesai') ?>">
                                <?= form_error('tanggal_selesai', '<div class="invalid-feedback">', '</div>') ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="penanggung_jawab" class="form-label">Penanggung Jawab *</label>
                            <input type="text" class="form-control <?= form_error('penanggung_jawab') ? 'is-invalid' : '' ?>" 
                                   id="penanggung_jawab" name="penanggung_jawab" 
                                   value="<?= set_value('penanggung_jawab') ?>" 
                                   placeholder="Nama penanggung jawab program">
                            <?= form_error('penanggung_jawab', '<div class="invalid-feedback">', '</div>') ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= site_url('programkerja') ?>" class="btn btn-secondary me-md-2">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Program
                            </button>
                        </div>

                    <?= form_close() ?>
                </div>
            </div>
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
    
    <script>
        // Set minimum date for tanggal selesai based on tanggal mulai
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            var startDate = this.value;
            document.getElementById('tanggal_selesai').min = startDate;
        });
    </script>
</body>
</html>
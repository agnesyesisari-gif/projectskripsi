<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Ibadah - <?= $nama_gereja ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Edit Jadwal Ibadah</h4>
                        <a href="<?= base_url('jadwal') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if(isset($validation)): ?>
                            <div class="alert alert-danger">
                                <?= $validation->listErrors() ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('jadwal/update/' . $jadwal['id']) ?>" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                               value="<?= $jadwal['tanggal'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jenis_ibadah" class="form-label">Jenis Ibadah</label>
                                        <select class="form-select" id="jenis_ibadah" name="jenis_ibadah" required>
                                            <option value="">Pilih Jenis Ibadah</option>
                                            <option value="Ibadah Minggu" <?= ($jadwal['jenis_ibadah'] == 'Ibadah Minggu') ? 'selected' : '' ?>>Ibadah Minggu</option>
                                            <option value="Ibadah Tukar Mimbar Klasis" <?= ($jadwal['jenis_ibadah'] == 'Ibadah Tukar Mimbar Klasis') ? 'selected' : '' ?>>Ibadah Tukar Mimbar Klasis</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                                        <input type="time" class="form-control" id="waktu_mulai" name="waktu_mulai" 
                                               value="<?= $jadwal['waktu_mulai'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                                        <input type="time" class="form-control" id="waktu_selesai" name="waktu_selesai" 
                                               value="<?= $jadwal['waktu_selesai'] ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="pemimpin_ibadah" class="form-label">Pemimpin Ibadah</label>
                                <input type="text" class="form-control" id="pemimpin_ibadah" name="pemimpin_ibadah" 
                                       value="<?= $jadwal['pemimpin_ibadah'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Aktif" <?= ($jadwal['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                    <option value="Selesai" <?= ($jadwal['status'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                    <option value="Dibatalkan" <?= ($jadwal['status'] == 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= $jadwal['keterangan'] ?></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= base_url('jadwal') ?>" class="btn btn-secondary me-md-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Update Jadwal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
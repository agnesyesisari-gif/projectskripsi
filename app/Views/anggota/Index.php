<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .table-actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="<?= site_url('anggota/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Anggota
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Foto</th>
                                        <th>Nama</th>
                                        <th>Jenis Kelamin</th>
                                        <th>No. Telepon</th>
                                        <th>Status</th>
                                        <th>Bidang Pelayanan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($anggota)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Tidak ada data anggota</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($anggota as $item): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td>
                                                    <img src="<?= base_url('uploads/anggota/' . $item['foto']) ?>" 
                                                         alt="Foto <?= $item['nama'] ?>" 
                                                         class="profile-img"
                                                         onerror="this.src='<?= base_url('uploads/anggota/default.png') ?>'">
                                                </td>
                                                <td><?= esc($item['nama']) ?></td>
                                                <td>
                                                    <?= $item['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                                </td>
                                                <td><?= esc($item['no_telepon']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $item['status_aktif'] ? 'success' : 'secondary' ?>">
                                                        <?= $item['status_aktif'] ? 'Aktif' : 'Tidak Aktif' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= $item['bidang_pelayanan'] ? esc($item['bidang_pelayanan']) : '-' ?>
                                                </td>
                                                <td class="table-actions">
                                                    <a href="<?= site_url('anggota/view/' . $item['id_anggota']) ?>" 
                                                       class="btn btn-sm btn-info" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= site_url('anggota/edit/' . $item['id_anggota']) ?>" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Hapus"
                                                            onclick="confirmDelete(<?= $item['id_anggota'] ?>, '<?= esc($item['nama_lengkap']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data anggota <strong id="deleteName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="deleteButton" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, name) {
            document.getElementById('deleteName').textContent = name;
            document.getElementById('deleteButton').href = '<?= site_url('anggota/delete/') ?>' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
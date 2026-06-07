<?php echo view('Layout/Header', ['title' => $title ?? 'Buat Notifikasi']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Buat Notifikasi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('notifikasi/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="judul" class="form-control" value="<?= old('judul') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pesan</label>
                            <textarea name="pesan" class="form-control" rows="4" required><?= old('pesan') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Penerima</label>
                            <select name="penerima" class="form-select">
                                <option value="semua">Semua Pengguna</option>
                                <option value="admin">Admin</option>
                                <option value="jemaat">Jemaat</option>
                                <option value="pengurus">Pengurus</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim</button>
                            <a href="<?= site_url('notifikasi') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

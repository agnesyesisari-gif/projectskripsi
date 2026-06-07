<?php echo view('Layout/Header', ['title' => $title ?? 'Lupa Password']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-unlock-alt me-2"></i>Lupa Password</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Masukkan email kamu, kami akan kirimkan link reset password.</p>
                    <form action="<?= site_url('password/send-reset') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i> Kirim Link Reset</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= site_url('login') ?>">Kembali ke Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registrasi</h4>
                </div>
                <div class="card-body">
                    <?php echo form_open('auth/register'); ?>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo set_value('nama'); ?>" required>
                            <?php echo form_error('nama', '<div class="text-danger">', '</div>'); ?>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email'); ?>" required>
                            <?php echo form_error('email', '<div class="text-danger">', '</div>'); ?>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <?php echo form_error('password', '<div class="text-danger">', '</div>'); ?>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <?php echo form_error('confirm_password', '<div class="text-danger">', '</div>'); ?>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </button>
                    <?php echo form_close(); ?>
                    
                    <div class="text-center mt-3">
                        <p>Sudah punya akun? <a href="<?php echo base_url('auth/login'); ?>">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
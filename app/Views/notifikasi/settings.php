<?php echo view('Layout/Header', ['title' => $title ?? 'Pengaturan Notifikasi']); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Pengaturan Notifikasi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('notifikasi/save-settings') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notifikasi Email</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="email_notif" id="emailNotif"
                                    <?= ($settings['email_notif'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="emailNotif">Aktifkan notifikasi via email</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notifikasi Kegiatan</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kegiatan_notif" id="kegiatanNotif"
                                    <?= ($settings['kegiatan_notif'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="kegiatanNotif">Notifikasi kegiatan baru</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notifikasi Jadwal Ibadah</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jadwal_notif" id="jadwalNotif"
                                    <?= ($settings['jadwal_notif'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="jadwalNotif">Notifikasi jadwal ibadah</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('Layout/Footer'); ?>

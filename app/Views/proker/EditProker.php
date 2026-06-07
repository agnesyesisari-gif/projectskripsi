<?= $this->extend('templates/main_template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 text-primary"><i class="fas fa-<?= $title_icon ?> me-2"></i><?= $title ?></h1>
        <p class="text-muted"><?= $subtitle ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= site_url('program') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Informasi Program</h5>
            </div>
            <div class="card-body">
                <?php if(isset($validation)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $validation->listErrors() ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Program <span class="text-danger">*</span></label>
                            <input type="text" name="nama_program" class="form-control" 
                                   value="<?= old('nama_program', $program['nama_program'] ?? '') ?>" 
                                   placeholder="Masukkan nama program" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Ibadah" <?= (old('kategori', $program['kategori'] ?? '') == 'Ibadah') ? 'selected' : '' ?>>Ibadah</option>
                                <option value="Pemuda" <?= (old('kategori', $program['kategori'] ?? '') == 'Pemuda') ? 'selected' : '' ?>>Pemuda</option>
                                <option value="Anak" <?= (old('kategori', $program['kategori'] ?? '') == 'Anak') ? 'selected' : '' ?>>Anak</option>
                                <option value="Wanita" <?= (old('kategori', $program['kategori'] ?? '') == 'Wanita') ? 'selected' : '' ?>>Wanita</option>
                                <option value="Adi Yuswa" <?= (old('kategori', $program['kategori'] ?? '') == 'Adi Yuswa') ? 'selected' : '' ?>>Adi Yuswa</option>
                                <option value="Pralaya" <?= (old('kategori', $program['kategori'] ?? '') == 'Pralaya') ? 'selected' : '' ?>>Pralaya</option>
                                <option value="Kehartaan" <?= (old('kategori', $program['kategori'] ?? '') == 'Kehartaan') ? 'selected' : '' ?>>Kehartaan</option>
                                <option value="Verifikasi" <?= (old('kategori', $program['kategori'] ?? '') == 'Verifikasi') ? 'selected' : '' ?>>Verifikasi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Program <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="4" 
                                  placeholder="Jelaskan detail program kerja" required><?= old('deskripsi', $program['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" 
                                   value="<?= old('tanggal_mulai', $program['tanggal_mulai'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" 
                                   value="<?= old('tanggal_selesai', $program['tanggal_selesai'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Penanggung Jawab <span class="text-danger">*</span></label>
                            <input type="text" name="penanggung_jawab" class="form-control" 
                                   value="<?= old('penanggung_jawab', $program['penanggung_jawab'] ?? '') ?>" 
                                   placeholder="Nama penanggung jawab" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Anggaran (Rp)</label>
                            <input type="number" name="anggaran" class="form-control" 
                                   value="<?= old('anggaran', $program['anggaran'] ?? '') ?>" 
                                   placeholder="0">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="">Pilih Status</option>
                                <option value="Perencanaan" <?= (old('status', $program['status'] ?? '') == 'Perencanaan') ? 'selected' : '' ?>>Perencanaan</option>
                                <option value="Berjalan" <?= (old('status', $program['status'] ?? '') == 'Berjalan') ? 'selected' : '' ?>>Berjalan</option>
                                <option value="Selesai" <?= (old('status', $program['status'] ?? '') == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                <option value="Ditunda" <?= (old('status', $program['status'] ?? '') == 'Ditunda') ? 'selected' : '' ?>>Ditunda</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lampiran</label>
                        <input type="file" name="lampiran" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                        <?php if(isset($program['lampiran']) && $program['lampiran']): ?>
                            <small class="text-muted">File terpasang: <?= $program['lampiran'] ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Program
                        </button>
                        <a href="<?= site_url('program') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Informasi Tambahan -->
        <div class="card card-shadow">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Petunjuk</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Tips:</h6>
                    <ul class="mb-0 ps-3">
                        <li>Isi semua field yang bertanda <span class="text-danger">*</span></li>
                        <li>Pastikan tanggal selesai lebih besar dari tanggal mulai</li>
                        <li>Lampiran maksimal 5MB (PDF, DOC, JPG, PNG)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Program Terkini -->
        <div class="card card-shadow mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Program Terbaru</h6>
            </div>
            <div class="card-body">
                <?php if($program_terbaru): ?>
                    <?php foreach($program_terbaru as $terbaru): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-1"><?= $terbaru['nama_program'] ?></h6>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?= date('d M Y', strtotime($terbaru['tanggal_mulai'])) ?>
                        </small>
                        <div>
                            <span class="badge bg-sm"><?= $terbaru['status'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Belum ada program</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Update progress value display
    document.querySelector('input[name="progress"]').addEventListener('input', function() {
        document.getElementById('progressValue').textContent = this.value + '%';
    });

    // Date validation
    document.querySelector('input[name="tanggal_mulai"]').addEventListener('change', function() {
        const endDate = document.querySelector('input[name="tanggal_selesai"]');
        if (this.value && endDate.value && this.value > endDate.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
            this.value = '';
        }
    });

    document.querySelector('input[name="tanggal_selesai"]').addEventListener('change', function() {
        const startDate = document.querySelector('input[name="tanggal_mulai"]');
        if (this.value && startDate.value && this.value < startDate.value) {
            alert('Tanggal selesai tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
        }
    });
</script>
<?= $this->endSection() ?>
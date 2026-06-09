<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box <?= $isEdit ? 'box-warning' : 'box-success' ?>">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i>
                    <?= $isEdit ? 'Edit' : 'Buat' ?> Pengumuman
                </h3>
                <a href="<?= base_url('pengumuman') ?>" class="btn btn-default btn-sm pull-right">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="box-body">
                <form action="<?= base_url($isEdit ? 'pengumuman/update/'.$data['id'] : 'pengumuman/simpan') ?>"
                      method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Judul Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control"
                               value="<?= esc(old('judul', $data['judul'] ?? '')) ?>"
                               placeholder="Judul pengumuman..." required>
                    </div>

                    <div class="form-group">
                        <label>Isi Pengumuman <span class="text-danger">*</span></label>
                        <textarea name="isi" class="form-control" rows="6"
                                  placeholder="Isi pengumuman..." required><?= esc(old('isi', $data['isi'] ?? '')) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Tayang</label>
                                <input type="date" name="tanggal_tayang" class="form-control"
                                       value="<?= esc(old('tanggal_tayang', $data['tanggal_tayang'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?= old('status', $data['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="aktif" <?= old('status', $data['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= old('status', $data['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn <?= $isEdit ? 'btn-warning' : 'btn-success' ?>">
                        <i class="fa fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                    <a href="<?= base_url('pengumuman') ?>" class="btn btn-default">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

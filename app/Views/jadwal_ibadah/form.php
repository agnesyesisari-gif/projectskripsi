<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box <?= $isEdit ? 'box-warning' : 'box-primary' ?>">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i>
                    <?= $isEdit ? 'Edit' : 'Tambah' ?> Jadwal Ibadah
                </h3>
                <a href="<?= base_url('jadwal') ?>" class="btn btn-default btn-sm pull-right">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="box-body">

                <form action="<?= base_url($isEdit ? 'jadwal/update/'.$data['id'] : 'jadwal/simpan') ?>"
                      method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Nama Ibadah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ibadah" class="form-control"
                               value="<?= esc(old('nama_ibadah', $data['nama_ibadah'] ?? '')) ?>"
                               placeholder="Contoh: Ibadah Minggu, Ibadah Pemuda..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control"
                                       value="<?= esc(old('tanggal', $data['tanggal'] ?? date('Y-m-d'))) ?>"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jam <span class="text-danger">*</span></label>
                                <input type="time" name="jam" class="form-control"
                                       value="<?= esc(old('jam', $data['jam'] ?? '07:00')) ?>"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lokasi / Tempat <span class="text-danger">*</span></label>
                        <input type="text" name="lokasi" class="form-control"
                               value="<?= esc(old('lokasi', $data['lokasi'] ?? '')) ?>"
                               placeholder="Contoh: Gedung Gereja GKJ Penaruban" required>
                    </div>

                    <div class="form-group">
                        <label>Petugas / Pengkhotbah</label>
                        <input type="text" name="petugas" class="form-control"
                               value="<?= esc(old('petugas', $data['petugas'] ?? '')) ?>"
                               placeholder="Nama pendeta atau petugas">
                    </div>

                    <div class="form-group">
                        <label>Komisi (opsional)</label>
                        <select name="komisi_id" class="form-control">
                            <option value="">-- Semua Komisi --</option>
                            <?php foreach ($komisiList as $k): ?>
                                <option value="<?= $k['id'] ?>"
                                    <?= old('komisi_id', $data['komisi_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                    <?= esc($k['nama_komisi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"
                                  placeholder="Keterangan tambahan..."><?= esc(old('keterangan', $data['keterangan'] ?? '')) ?></textarea>
                    </div>

                    <hr>
                    <button type="submit" class="btn <?= $isEdit ? 'btn-warning' : 'btn-primary' ?>">
                        <i class="fa fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                    <a href="<?= base_url('jadwal') ?>" class="btn btn-default">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$namaBulan  = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
$statusList = ['rencana'=>'Rencana','berjalan'=>'Berjalan','selesai'=>'Selesai','batal'=>'Batal'];
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box <?= $isEdit ? 'box-warning' : 'box-primary' ?>">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i>
                    <?= $isEdit ? 'Edit' : 'Tambah' ?> Program Kerja
                </h3>
                <a href="<?= base_url('program') ?>" class="btn btn-default btn-sm pull-right">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="box-body">
                <form action="<?= base_url($isEdit ? 'program/update/'.$data['id'] : 'program/simpan') ?>"
                      method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Nama Program Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="nama_program" class="form-control"
                               value="<?= esc(old('nama_program', $data['nama_program'] ?? '')) ?>"
                               placeholder="Contoh: Retreat Pemuda 2025..." required>
                    </div>

                    <div class="form-group">
                        <label>Komisi <span class="text-danger">*</span></label>
                        <select name="komisi_id" class="form-control" required>
                            <option value="">-- Pilih Komisi --</option>
                            <?php foreach ($komisiList as $k): ?>
                                <option value="<?= $k['id'] ?>"
                                    <?= old('komisi_id', $data['komisi_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                    <?= esc($k['nama_komisi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bulan Pelaksanaan</label>
                                <select name="bulan" class="form-control">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>"
                                            <?= old('bulan', $data['bulan'] ?? date('n')) == $i ? 'selected' : '' ?>>
                                            <?= $namaBulan[$i] ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tahun</label>
                                <select name="tahun" class="form-control">
                                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 3; $y++): ?>
                                        <option value="<?= $y ?>"
                                            <?= old('tahun', $data['tahun'] ?? date('Y')) == $y ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <?php foreach ($statusList as $k => $v): ?>
                                        <option value="<?= $k ?>"
                                            <?= old('status', $data['status'] ?? 'rencana') === $k ? 'selected' : '' ?>>
                                            <?= $v ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Anggaran (Rp)</label>
                        <input type="number" name="anggaran" class="form-control"
                               value="<?= esc(old('anggaran', $data['anggaran'] ?? '')) ?>"
                               placeholder="Contoh: 5000000" min="0">
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"
                                  placeholder="Deskripsi program..."><?= esc(old('keterangan', $data['keterangan'] ?? '')) ?></textarea>
                    </div>

                    <hr>
                    <button type="submit" class="btn <?= $isEdit ? 'btn-warning' : 'btn-primary' ?>">
                        <i class="fa fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                    <a href="<?= base_url('program') ?>" class="btn btn-default">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

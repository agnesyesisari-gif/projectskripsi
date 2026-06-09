<div class="row">
    <!-- Form -->
    <div class="col-md-4">
        <div class="box <?= $editData ? 'box-warning' : 'box-primary' ?>">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-<?= $editData ? 'edit' : 'plus' ?>"></i>
                    <?= $editData ? 'Edit' : 'Tambah' ?> Komisi
                </h3>
                <?php if ($editData): ?>
                    <a href="<?= base_url('komisi') ?>" class="btn btn-default btn-xs pull-right">Batal</a>
                <?php endif; ?>
            </div>
            <div class="box-body">
                <form action="<?= base_url($editData ? 'komisi/update/'.$editData['id'] : 'komisi/simpan') ?>"
                      method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Nama Komisi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_komisi" class="form-control"
                               value="<?= esc(old('nama_komisi', $editData['nama_komisi'] ?? '')) ?>"
                               placeholder="Contoh: Komisi Anak, Pemuda..." required>
                    </div>
                    <div class="form-group">
                        <label>Ketua Komisi</label>
                        <input type="text" name="ketua" class="form-control"
                               value="<?= esc(old('ketua', $editData['ketua'] ?? '')) ?>"
                               placeholder="Nama ketua">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"
                                  placeholder="Keterangan komisi..."><?= esc(old('deskripsi', $editData['deskripsi'] ?? '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn <?= $editData ? 'btn-warning' : 'btn-primary' ?> btn-sm">
                        <i class="fa fa-save"></i> <?= $editData ? 'Perbarui' : 'Simpan' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-users"></i> Daftar Komisi</h3>
            </div>
            <div class="box-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" style="font-size:13px;margin:0;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Komisi</th>
                                <th>Ketua</th>
                                <th>Deskripsi</th>
                                <th>Prog.</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($komisiList)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data komisi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($komisiList as $i => $k): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= esc($k['nama_komisi']) ?></strong></td>
                                        <td><?= esc($k['ketua'] ?? '-') ?></td>
                                        <td><?= esc(mb_strimwidth($k['deskripsi'] ?? '-', 0, 40, '...')) ?></td>
                                        <td>
                                            <span class="badge" style="background:#0073b7;">
                                                <?= $k['total_program'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td nowrap>
                                            <a href="<?= base_url('komisi/edit/'.$k['id']) ?>"
                                               class="btn btn-warning btn-xs">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('komisi/hapus/'.$k['id']) ?>"
                                               class="btn btn-danger btn-xs"
                                               onclick="return confirm('Hapus komisi ini?')">
                                                <i class="fa fa-trash"></i>
                                            </a>
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

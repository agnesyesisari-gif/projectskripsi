<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-bullhorn"></i> Pengumuman Gereja</h3>
        <a href="<?= base_url('pengumuman/tambah') ?>" class="btn btn-success btn-sm pull-right">
            <i class="fa fa-plus"></i> Buat Pengumuman
        </a>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Isi</th>
                        <th>Tanggal Tayang</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fa fa-inbox"></i> Belum ada pengumuman.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $stCls = ['draft'=>'default','aktif'=>'success','nonaktif'=>'danger'];
                        foreach ($list as $i => $p):
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= esc($p['judul']) ?></strong></td>
                                <td><?= esc(mb_strimwidth($p['isi'], 0, 60, '...')) ?></td>
                                <td>
                                    <?= !empty($p['tanggal_tayang'])
                                        ? date('d/m/Y', strtotime($p['tanggal_tayang'])) : '-' ?>
                                </td>
                                <td>
                                    <span class="label label-<?= $stCls[$p['status']] ?? 'default' ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                <td nowrap>
                                    <a href="<?= base_url('pengumuman/edit/'.$p['id']) ?>"
                                       class="btn btn-warning btn-xs">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('whatsapp') ?>"
                                       class="btn btn-success btn-xs" title="Kirim via WA">
                                        <i class="fa fa-whatsapp"></i>
                                    </a>
                                    <a href="<?= base_url('pengumuman/hapus/'.$p['id']) ?>"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('Hapus pengumuman ini?')">
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

<?php
$namaBulan  = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
$statusList = ['rencana'=>'Rencana','berjalan'=>'Berjalan','selesai'=>'Selesai','batal'=>'Batal'];
$badgeCls   = ['rencana'=>'default','berjalan'=>'info','selesai'=>'success','batal'=>'danger'];
?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-tasks"></i> Program Kerja Per Komisi</h3>
        <a href="<?= base_url('program/tambah') ?>" class="btn btn-primary btn-sm pull-right">
            <i class="fa fa-plus"></i> Tambah Program
        </a>
    </div>
    <div class="box-body">

        <!-- Filter -->
        <form method="GET" action="<?= base_url('program') ?>"
              class="form-inline" style="margin-bottom:12px;gap:5px;display:flex;flex-wrap:wrap;">
            <select name="komisi_id" class="form-control input-sm">
                <option value="">-- Semua Komisi --</option>
                <?php foreach ($komisiList as $k): ?>
                    <option value="<?= $k['id'] ?>"
                        <?= ($filter['komisi_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                        <?= esc($k['nama_komisi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="tahun" class="form-control input-sm">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y ?>" <?= ($filter['tahun'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select name="status" class="form-control input-sm">
                <option value="">-- Semua Status --</option>
                <?php foreach ($statusList as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($filter['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" class="form-control input-sm"
                   placeholder="Cari program..." value="<?= esc($filter['search'] ?? '') ?>" style="width:160px;">
            <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-search"></i> Filter</button>
            <a href="<?= base_url('program') ?>" class="btn btn-default btn-sm">Reset</a>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Program</th>
                        <th>Komisi</th>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th>Anggaran</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fa fa-inbox"></i> Belum ada data program kerja.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= esc($p['nama_program']) ?></strong></td>
                                <td>
                                    <?php if ($p['nama_komisi']): ?>
                                        <span class="label label-info"><?= esc($p['nama_komisi']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $namaBulan[$p['bulan']] ?? '-' ?></td>
                                <td><?= $p['tahun'] ?></td>
                                <td>
                                    <?= $p['anggaran'] ? 'Rp '.number_format($p['anggaran'], 0, ',', '.') : '-' ?>
                                </td>
                                <td>
                                    <span class="label label-<?= $badgeCls[$p['status']] ?? 'default' ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td><?= esc(mb_strimwidth($p['keterangan'] ?? '-', 0, 40, '...')) ?></td>
                                <td nowrap>
                                    <a href="<?= base_url('program/edit/'.$p['id']) ?>"
                                       class="btn btn-warning btn-xs">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('program/hapus/'.$p['id']) ?>"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('Hapus program ini?')">
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

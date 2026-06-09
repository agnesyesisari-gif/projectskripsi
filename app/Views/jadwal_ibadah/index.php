<?php
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Data Jadwal Ibadah</h3>
        <a href="<?= base_url('jadwal/tambah') ?>" class="btn btn-primary btn-sm pull-right">
            <i class="fa fa-plus"></i> Tambah Jadwal
        </a>
    </div>
    <div class="box-body">

        <!-- Filter -->
        <form method="GET" action="<?= base_url('jadwal') ?>" class="form-inline" style="margin-bottom:12px;">
            <div class="form-group" style="margin-right:5px;">
                <select name="bulan" class="form-control input-sm">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>>
                            <?= $namaBulan[$i] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin-right:5px;">
                <select name="tahun" class="form-control input-sm">
                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                        <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin-right:5px;">
                <input type="text" name="search" class="form-control input-sm"
                       placeholder="Cari nama / lokasi..." value="<?= esc($search) ?>" style="width:180px;">
            </div>
            <button type="submit" class="btn btn-info btn-sm">
                <i class="fa fa-search"></i> Filter
            </button>
            <a href="<?= base_url('jadwal') ?>" class="btn btn-default btn-sm">Reset</a>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Ibadah</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Lokasi</th>
                        <th>Petugas</th>
                        <th>Komisi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwals)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fa fa-inbox"></i> Belum ada jadwal ibadah.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jadwals as $i => $j): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= esc($j['nama_ibadah']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($j['tanggal'])) ?></td>
                                <td><?= substr($j['jam'], 0, 5) ?> WIB</td>
                                <td><?= esc($j['lokasi']) ?></td>
                                <td><?= esc($j['petugas'] ?? '-') ?></td>
                                <td>
                                    <?php if ($j['nama_komisi']): ?>
                                        <span class="label label-info"><?= esc($j['nama_komisi']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc(mb_strimwidth($j['keterangan'] ?? '-', 0, 40, '...')) ?></td>
                                <td nowrap>
                                    <a href="<?= base_url('jadwal/edit/'.$j['id']) ?>"
                                       class="btn btn-warning btn-xs">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('jadwal/hapus/'.$j['id']) ?>"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('Hapus jadwal ini?')">
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

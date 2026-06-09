<!-- ROW 1 -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?= $stats['jadwal'] ?></h3>
                <p>JADWAL IBADAH</p>
            </div>
            <div class="icon"><i class="fa fa-calendar"></i></div>
            <a href="<?= base_url('jadwal') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?= $stats['jadwal_minggu'] ?></h3>
                <p>IBADAH MINGGU INI</p>
            </div>
            <div class="icon"><i class="fa fa-clock-o"></i></div>
            <a href="<?= base_url('jadwal') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?= $stats['program'] ?></h3>
                <p>PROGRAM KERJA</p>
            </div>
            <div class="icon"><i class="fa fa-tasks"></i></div>
            <a href="<?= base_url('program') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3><?= $stats['komisi'] ?></h3>
                <p>KOMISI</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
            <a href="<?= base_url('komisi') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ROW 2 -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?= $stats['prog_aktif'] ?></h3>
                <p>PROGRAM AKTIF <?= date('Y') ?></p>
            </div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
            <a href="<?= base_url('program') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?= $stats['pengumuman'] ?></h3>
                <p>PENGUMUMAN</p>
            </div>
            <div class="icon"><i class="fa fa-bullhorn"></i></div>
            <a href="<?= base_url('pengumuman') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3><?= $stats['user'] ?></h3>
                <p>USER</p>
            </div>
            <div class="icon"><i class="fa fa-user-secret"></i></div>
            <a href="#" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?= $stats['jadwal'] ?></h3>
                <p>TOTAL IBADAH</p>
            </div>
            <div class="icon"><i class="fa fa-church"></i></div>
            <a href="<?= base_url('jadwal') ?>" class="small-box-footer">
                More <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ACTION BAR -->
<div class="action-bar">
    <a href="<?= base_url('pengumuman') ?>" class="btn btn-success btn-sm">
        <i class="fa fa-bullhorn"></i>&nbsp; PENGUMUMAN
    </a>
    <a href="<?= base_url('backup') ?>" class="btn btn-primary btn-sm">
        <i class="fa fa-database"></i>&nbsp; BACKUP
    </a>
</div>

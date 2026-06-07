<?= $this->extend('templates/main') ?>

<?= $this->section('content') ?>
<!-- Main Content -->
<div class="container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                    Dashboard Sistem Informasi Gereja
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Beranda</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-2"></i>Aksi Cepat
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= base_url('jadwal/tambah') ?>">
                            <i class="fas fa-plus-circle text-success me-2"></i>Tambah Jadwal
                        </a></li>
                        <li><a class="dropdown-item" href="<?= base_url('program/tambah') ?>">
                            <i class="fas fa-tasks text-warning me-2"></i>Tambah Program
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('laporan') ?>">
                            <i class="fas fa-file-pdf text-danger me-2"></i>Cetak Laporan
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Banner -->
    <div class="alert alert-gradient alert-dismissible fade show" role="alert">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="fas fa-church fa-2x"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-1">Selamat datang di Sistem Informasi Gereja!</h5>
                <p class="mb-0">Halo, <strong><?= esc(session()->get('nama')) ?></strong>! Anda login sebagai 
                    <span class="badge bg-primary"><?= esc(session()->get('role')) ?></span>. 
                    <?php if(session()->get('role') == 'admin'): ?>
                        Anda memiliki akses penuh untuk mengelola sistem.
                    <?php elseif(session()->get('role') == 'pengurus'): ?>
                        Anda dapat mengelola jadwal dan program kegiatan.
                    <?php else: ?>
                        Anda dapat melihat jadwal dan program kegiatan.
                    <?php endif; ?>
                </p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-primary-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Total Jadwal Ibadah</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_jadwal']) ?></h2>
                            <p class="card-text small mb-0">
                                <i class="fas fa-arrow-up me-1"></i>
                                <?= $stats['jadwal_minggu_ini'] ?> jadwal minggu ini
                            </p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="<?= base_url('jadwal') ?>" class="card-footer text-white d-flex justify-content-between align-items-center">
                    <span>Lihat detail</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-success-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Program Aktif</h6>
                            <h2 class="mb-0"><?= number_format($stats['program_aktif']) ?></h2>
                            <p class="card-text small mb-0">
                                <i class="fas fa-check-circle me-1"></i>
                                Dari <?= $stats['total_program'] ?> total program
                            </p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-tasks fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="<?= base_url('program') ?>" class="card-footer text-white d-flex justify-content-between align-items-center">
                    <span>Lihat detail</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-warning-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Total Pelayan</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_pelayan']) ?></h2>
                            <p class="card-text small mb-0">
                                <i class="fas fa-user-check me-1"></i>
                                <?= $stats['pelayan_aktif'] ?> aktif
                            </p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="<?= base_url('pelayan') ?>" class="card-footer text-white d-flex justify-content-between align-items-center">
                    <span>Lihat detail</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-info-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Kegiatan Mendatang</h6>
                            <h2 class="mb-0"><?= number_format($stats['kegiatan_mendatang']) ?></h2>
                            <p class="card-text small mb-0">
                                <i class="fas fa-clock me-1"></i>
                                Dalam 7 hari ke depan
                            </p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-church fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="<?= base_url('jadwal/mendatang') ?>" class="card-footer text-white d-flex justify-content-between align-items-center">
                    <span>Lihat detail</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Jadwal Ibadah Hari Ini -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-day me-2"></i>
                            Jadwal Ibadah Hari Ini (<?= date('d F Y') ?>)
                        </h5>
                        <a href="<?= base_url('jadwal') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-list me-1"></i> Semua Jadwal
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($jadwal_hari_ini)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Jenis Ibadah</th>
                                        <th>Tempat</th>
                                        <th>Pemimpin</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($jadwal_hari_ini as $jadwal): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-clock text-primary me-1"></i>
                                            <?= date('H:i', strtotime($jadwal['waktu_mulai'])) ?>
                                        </td>
                                        <td>
                                            <strong><?= esc($jadwal['jenis_ibadah']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= esc($jadwal['tema']) ?></small>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            <?= esc($jadwal['tempat']) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title bg-info rounded-circle">
                                                        <?= substr($jadwal['pemimpin'], 0, 1) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <small><?= esc($jadwal['pemimpin']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            $status_icon = '';
                                            switch($jadwal['status']):
                                                case 'belum_dimulai':
                                                    $status_class = 'warning';
                                                    $status_icon = 'clock';
                                                    break;
                                                case 'sedang_berlangsung':
                                                    $status_class = 'success';
                                                    $status_icon = 'play-circle';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'secondary';
                                                    $status_icon = 'check-circle';
                                                    break;
                                            endswitch;
                                            ?>
                                            <span class="badge bg-<?= $status_class ?>">
                                                <i class="fas fa-<?= $status_icon ?> me-1"></i>
                                                <?= ucfirst(str_replace('_', ' ', $jadwal['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= base_url('jadwal/detail/' . $jadwal['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if(session()->get('role') == 'admin' || session()->get('role') == 'pengurus'): ?>
                                                <a href="<?= base_url('jadwal/edit/' . $jadwal['id']) ?>" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada jadwal ibadah hari ini</h5>
                            <a href="<?= base_url('jadwal/tambah') ?>" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>Tambah Jadwal
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kalender & Program Aktif -->
        <div class="col-lg-4">
            <!-- Mini Calendar -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Kalender Kegiatan
                    </h5>
                </div>
                <div class="card-body">
                    <div id="mini-calendar"></div>
                    <div class="mt-3">
                        <a href="<?= base_url('jadwal/kalender') ?>" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-expand-alt me-1"></i> Lihat Kalender Lengkap
                        </a>
                    </div>
                </div>
            </div>

            <!-- Program Kerja Aktif -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Program Kerja Aktif
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($program_aktif)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($program_aktif as $program): ?>
                            <a href="<?= base_url('program/detail/' . $program['id']) ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= esc($program['nama_program']) ?></h6>
                                    <small>
                                        <span class="badge bg-<?= ($program['progress'] == 100) ? 'success' : 'primary' ?>">
                                            <?= $program['progress'] ?>%
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1 small text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <?= esc($program['penanggung_jawab']) ?>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d M Y', strtotime($program['tanggal_mulai'])) ?>
                                    - 
                                    <?= date('d M Y', strtotime($program['tanggal_selesai'])) ?>
                                </small>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-<?= ($program['progress'] == 100) ? 'success' : 'primary' ?>" 
                                         role="progressbar" 
                                         style="width: <?= $program['progress'] ?>%">
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <a href="<?= base_url('program') ?>" class="btn btn-warning btn-sm w-100">
                                <i class="fas fa-list me-1"></i> Lihat Semua Program
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Tidak ada program aktif</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Mendatang -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-week me-2"></i>
                            Jadwal Ibadah 7 Hari Mendatang
                        </h5>
                        <a href="<?= base_url('jadwal/mendatang') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($jadwal_mendatang)): ?>
                        <div class="row">
                            <?php foreach($jadwal_mendatang as $jadwal): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card schedule-card h-100">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-church text-primary me-2"></i>
                                                <?= esc($jadwal['jenis_ibadah']) ?>
                                            </h6>
                                            <span class="badge bg-info">
                                                <?= date('d M', strtotime($jadwal['tanggal'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= esc($jadwal['tema']) ?></h5>
                                        <p class="card-text small text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('H:i', strtotime($jadwal['waktu_mulai'])) ?>
                                            - 
                                            <?= date('H:i', strtotime($jadwal['waktu_selesai'])) ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            <?= esc($jadwal['tempat']) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <?= esc($jadwal['pemimpin']) ?>
                                            </small>
                                            <a href="<?= base_url('jadwal/detail/' . $jadwal['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada jadwal ibadah dalam 7 hari mendatang</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Akses Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <a href="<?= base_url('jadwal/tambah') ?>" class="btn btn-primary btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-plus fa-2x mb-2"></i>
                                <span>Jadwal Baru</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <a href="<?= base_url('program/tambah') ?>" class="btn btn-success btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <span>Program Baru</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <a href="<?= base_url('laporan') ?>" class="btn btn-warning btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-file-pdf fa-2x mb-2"></i>
                                <span>Laporan</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <a href="<?= base_url('jadwal/kalender') ?>" class="btn btn-info btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <span>Kalender</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <a href="<?= base_url('pengumuman') ?>" class="btn btn-danger btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                <span>Pengumuman</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <a href="<?= base_url('pengaturan') ?>" class="btn btn-dark btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-cog fa-2x mb-2"></i>
                                <span>Pengaturan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/id.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">

<script>
$(document).ready(function() {
    // Inisialisasi mini calendar
    var calendarEl = document.getElementById('mini-calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: 250,
        headerToolbar: {
            left: 'title',
            center: '',
            right: 'prev,next'
        },
        events: <?= json_encode($calendar_events) ?>,
        eventClick: function(info) {
            window.location.href = '<?= base_url("jadwal/detail") ?>/' + info.event.id;
        }
    });
    calendar.render();

    // Update jam real-time
    function updateClock() {
        var now = new Date();
        var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        var day = days[now.getDay()];
        var date = now.getDate();
        var month = months[now.getMonth()];
        var year = now.getFullYear();
        var hours = now.getHours().toString().padStart(2, '0');
        var minutes = now.getMinutes().toString().padStart(2, '0');
        var seconds = now.getSeconds().toString().padStart(2, '0');
        
        $('.real-time-clock').text(hours + ':' + minutes + ':' + seconds);
        $('.real-time-date').text(day + ', ' + date + ' ' + month + ' ' + year);
    }
    
    setInterval(updateClock, 1000);
    updateClock();

    // Notifikasi untuk jadwal mendatang
    <?php if(!empty($jadwal_hari_ini)): ?>
        <?php foreach($jadwal_hari_ini as $jadwal): ?>
            <?php if($jadwal['status'] == 'belum_dimulai'): ?>
                var jadwalTime = new Date('<?= $jadwal['tanggal'] ?> <?= $jadwal['waktu_mulai'] ?>');
                var now = new Date();
                var diff = jadwalTime - now;
                
                if(diff > 0 && diff < 3600000) { // Kurang dari 1 jam
                    setTimeout(function() {
                        showNotification('Jadwal Ibadah Akan Dimulai', 
                            '<?= $jadwal["jenis_ibadah"] ?> akan dimulai dalam 1 jam', 
                            'info');
                    }, diff - 3600000);
                }
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
});

function showNotification(title, message, type) {
    // Buat notifikasi menggunakan toast Bootstrap
    var toast = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    $('.toast-container').append(toast);
    $('.toast').last().toast('show');
}
</script>
<?= $this->endSection() ?>
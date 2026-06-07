<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Jadwal Ibadah - <?= $nama_gereja ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --success-color: #27ae60;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar {
            background-color: var(--primary-color);
        }
        
        .sidebar {
            background-color: var(--primary-color);
            min-height: calc(100vh - 56px);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .calendar-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1.5rem;
        }
        
        .fc-toolbar {
            margin-bottom: 1rem !important;
        }
        
        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .fc-button {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }
        
        .fc-button:hover {
            background-color: #2980b9 !important;
            border-color: #2980b9 !important;
        }
        
        .fc-button-active {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        
        .fc-event {
            border-radius: 0.25rem;
            padding: 0.125rem 0.25rem;
            font-size: 0.85rem;
        }
        
        .event-ibadah {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .event-pelayanan {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .event-khusus {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .event-lainnya {
            background-color: #f39c12;
            border-color: #f39c12;
        }
        
        .event-details {
            font-size: 0.9rem;
        }
        
        .event-time {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .event-location {
            color: #7f8c8d;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .upcoming-events .list-group-item {
            border-left: none;
            border-right: none;
            padding: 0.75rem 1rem;
        }
        
        .upcoming-events .list-group-item:first-child {
            border-top: none;
        }
        
        .upcoming-events .list-group-item:last-child {
            border-bottom: none;
        }
        
        .badge-jenis {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .filter-options .form-check {
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-church me-2"></i>
                <?= $nama_gereja ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('dashboard') ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= site_url('kalender') ?>">
                                <i class="fas fa-calendar-alt"></i> Kalender Ibadah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('jadwal') ?>">
                                <i class="fas fa-list"></i> Daftar Jadwal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('program-kerja') ?>">
                                <i class="fas fa-tasks"></i> Program Kerja
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('pengurus') ?>">
                                <i class="fas fa-users"></i> Pengurus
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('jemaat') ?>">
                                <i class="fas fa-user-friends"></i> Data Jemaat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('laporan') ?>">
                                <i class="fas fa-chart-bar"></i> Laporan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-calendar-alt me-2"></i> Kalender Jadwal Ibadah</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#tambahJadwalModal">
                            <i class="fas fa-plus me-1"></i> Tambah Jadwal
                        </button>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="todayBtn">Hari Ini</button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-9">
                        <div class="calendar-container">
                            <div id="calendar"></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <!-- Filter Jenis Kegiatan -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-filter me-2"></i> Filter Kegiatan
                            </div>
                            <div class="card-body filter-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="ibadah" id="filterIbadah" checked>
                                    <label class="form-check-label" for="filterIbadah">
                                        <span class="badge bg-primary badge-jenis">Ibadah</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="pelayanan" id="filterPelayanan" checked>
                                    <label class="form-check-label" for="filterPelayanan">
                                        <span class="badge bg-success badge-jenis">Pelayanan</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="khusus" id="filterKhusus" checked>
                                    <label class="form-check-label" for="filterKhusus">
                                        <span class="badge bg-danger badge-jenis">Khusus</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="lainnya" id="filterLainnya" checked>
                                    <label class="form-check-label" for="filterLainnya">
                                        <span class="badge bg-warning text-dark badge-jenis">Lainnya</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Jadwal Mendatang -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-clock me-2"></i> Jadwal Mendatang
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush upcoming-events">
                                    <?php if (!empty($jadwal_mendatang)): ?>
                                        <?php foreach ($jadwal_mendatang as $jadwal): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?= $jadwal['judul'] ?></h6>
                                                    <small class="text-muted"><?= $jadwal['tanggal'] ?></small>
                                                </div>
                                                <p class="mb-1 event-details">
                                                    <span class="event-time"><?= $jadwal['waktu'] ?></span>
                                                    <?php if (!empty($jadwal['lokasi'])): ?>
                                                        <span class="event-location"> - <?= $jadwal['lokasi'] ?></span>
                                                    <?php endif; ?>
                                                </p>
                                                <small>
                                                    <span class="badge bg-<?= $jadwal['jenis_badge'] ?> badge-jenis"><?= $jadwal['jenis'] ?></span>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item text-center text-muted">
                                            Tidak ada jadwal mendatang
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Jadwal -->
    <div class="modal fade" id="tambahJadwalModal" tabindex="-1" aria-labelledby="tambahJadwalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahJadwalModalLabel">Tambah Jadwal Ibadah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formTambahJadwal" action="<?= site_url('kalender/tambah') ?>" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Kegiatan</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis Kegiatan</label>
                            <select class="form-select" id="jenis" name="jenis" required>
                                <option value="ibadah">Ibadah</option>
                                <option value="pelayanan">Pelayanan</option>
                                <option value="khusus">Khusus</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="waktu" class="form-label">Waktu</label>
                                <input type="time" class="form-control" id="waktu" name="waktu" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi">
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                            <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi kalender
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: <?= json_encode($events) ?>,
                eventClick: function(info) {
                    // Tampilkan detail event
                    alert('Event: ' + info.event.title + '\nTanggal: ' + info.event.start.toLocaleDateString('id-ID'));
                },
                eventClassNames: function(arg) {
                    return ['event-' + arg.event.extendedProps.jenis];
                }
            });
            
            calendar.render();
            
            // Tombol hari ini
            document.getElementById('todayBtn').addEventListener('click', function() {
                calendar.today();
            });
            
            // Filter events berdasarkan jenis
            const filterCheckboxes = document.querySelectorAll('.filter-options input[type="checkbox"]');
            filterCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const jenis = this.value;
                    const events = calendar.getEvents();
                    
                    events.forEach(event => {
                        if (event.extendedProps.jenis === jenis) {
                            event.setProp('display', this.checked ? 'auto' : 'none');
                        }
                    });
                });
            });
            
            // Set tanggal default untuk form tambah jadwal
            document.getElementById('tanggal').valueAsDate = new Date();
        });
    </script>
</body>
</html>
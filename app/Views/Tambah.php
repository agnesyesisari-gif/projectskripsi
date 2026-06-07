<?php
session_start();
require_once '../config/database.php';
require_once '../models/JadwalModel.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Inisialisasi model
$jadwalModel = new JadwalModel();

// Ambil data berdasarkan jenis jadwal
$jenis_jadwal = isset($_GET['jenis']) ? $_GET['jenis'] : 'ibadah_minggu';

// Validasi jenis jadwal
$jenis_valid = ['ibadah_minggu', 'tukar_mimbar', 'program_kerja'];
if (!in_array($jenis_jadwal, $jenis_valid)) {
    $jenis_jadwal = 'ibadah_minggu';
}

// Ambil data sesuai jenis
$data_jadwal = [];
switch ($jenis_jadwal) {
    case 'ibadah_minggu':
        $data_jadwal = $jadwalModel->getJadwalIbadahMinggu();
        $judul = "Jadwal Ibadah Minggu";
        break;
    case 'tukar_mimbar':
        $data_jadwal = $jadwalModel->getJadwalTukarMimbar();
        $judul = "Jadwal Tukar Mimbar Klasis";
        break;
    case 'program_kerja':
        $data_jadwal = $jadwalModel->getProgramKerjaKomisi();
        $judul = "Program Kerja Komisi";
        break;
}

// Ambil komisi untuk filter (jika ada)
$komisi_list = $jadwalModel->getAllKomisi();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul; ?> - Gereja <?php echo $_SESSION['gereja_nama']; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 56px);
            box-shadow: 1px 0 5px rgba(0,0,0,0.1);
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card-custom:hover {
            transform: translateY(-5px);
        }
        
        .card-header-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .badge-jadwal {
            font-size: 0.8em;
            padding: 5px 10px;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }
        
        .table th {
            background-color: var(--light-color);
            font-weight: 600;
        }
        
        .filter-section {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-custom {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .btn-custom:hover {
            background-color: #2980b9;
            color: white;
        }
        
        .btn-print {
            background-color: var(--accent-color);
            color: white;
        }
        
        .nav-tabs-custom .nav-link {
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .nav-tabs-custom .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        .calendar-icon {
            color: var(--secondary-color);
        }
        
        .role-badge {
            font-size: 0.75em;
            padding: 3px 8px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .main-content {
                padding: 0;
            }
            
            .card-custom {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-church me-2"></i>
                Gereja <?php echo $_SESSION['gereja_nama']; ?>
            </a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3 d-none d-md-block">
                    <i class="fas fa-user me-1"></i> <?php echo $_SESSION['user_nama']; ?>
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar no-print">
                <div class="p-3">
                    <h5 class="mb-3 text-muted">Menu Pelayanan</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link <?php echo $jenis_jadwal == 'ibadah_minggu' ? 'active' : ''; ?>" 
                               href="?jenis=ibadah_minggu">
                                <i class="fas fa-calendar-alt me-2"></i> Jadwal Ibadah Minggu
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link <?php echo $jenis_jadwal == 'tukar_mimbar' ? 'active' : ''; ?>" 
                               href="?jenis=tukar_mimbar">
                                <i class="fas fa-exchange-alt me-2"></i> Tukar Mimbar Klasis
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link <?php echo $jenis_jadwal == 'program_kerja' ? 'active' : ''; ?>" 
                               href="?jenis=program_kerja">
                                <i class="fas fa-tasks me-2"></i> Program Kerja Komisi
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <div class="dropdown">
                                <button class="btn btn-custom w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-plus me-2"></i> Tambah Jadwal
                                </button>
                                <ul class="dropdown-menu w-100">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalTambahIbadah">
                                        <i class="fas fa-calendar-plus me-2"></i> Ibadah Minggu
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalTambahTukarMimbar">
                                        <i class="fas fa-exchange-alt me-2"></i> Tukar Mimbar
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalTambahProgram">
                                        <i class="fas fa-tasks me-2"></i> Program Kerja
                                    </a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1"><?php echo $judul; ?></h1>
                        <p class="text-muted">Kelola jadwal pelayanan dan kegiatan gereja</p>
                    </div>
                    <div class="no-print">
                        <button class="btn btn-print me-2" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Cetak
                        </button>
                        <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalExport">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section no-print">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Filter Tanggal</label>
                            <input type="text" class="form-control datepicker" id="filterTanggal" placeholder="Pilih tanggal">
                        </div>
                        <?php if ($jenis_jadwal == 'program_kerja'): ?>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Filter Komisi</label>
                            <select class="form-select" id="filterKomisi">
                                <option value="">Semua Komisi</option>
                                <?php foreach ($komisi_list as $komisi): ?>
                                    <option value="<?php echo $komisi['id']; ?>">
                                        <?php echo htmlspecialchars($komisi['nama_komisi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="selesai">Selesai</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button class="btn btn-custom w-100" onclick="applyFilters()">
                                <i class="fas fa-filter me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar <?php echo $judul; ?></h5>
                                <span class="badge bg-light text-dark">
                                    Total: <?php echo count($data_jadwal); ?> Data
                                </span>
                            </div>
                            <div class="card-body">
                                <!-- Tabs Navigation -->
                                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="jadwalTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button">
                                            <i class="fas fa-table me-1"></i> Tabel
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button">
                                            <i class="fas fa-calendar me-1"></i> Kalender
                                        </button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="jadwalTabContent">
                                    <!-- Table View -->
                                    <div class="tab-pane fade show active" id="table-view">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="tableJadwal">
                                                <thead>
                                                    <tr>
                                                        <?php if ($jenis_jadwal == 'ibadah_minggu'): ?>
                                                            <th width="15%">Tanggal</th>
                                                            <th width="15%">Waktu</th>
                                                            <th>Pemimpin Ibadah</th>
                                                            <th>Petugas Musik</th>
                                                            <th width="10%">Status</th>
                                                            <th width="10%" class="no-print">Aksi</th>
                                                        <?php elseif ($jenis_jadwal == 'tukar_mimbar'): ?>
                                                            <th width="15%">Tanggal</th>
                                                            <th>Gereja Pengirim</th>
                                                            <th>Gereja Penerima</th>
                                                            <th>Pemimpin Ibadah</th>
                                                            <th>Tema</th>
                                                            <th width="10%">Status</th>
                                                            <th width="10%" class="no-print">Aksi</th>
                                                        <?php else: ?>
                                                            <th width="15%">Tanggal Mulai</th>
                                                            <th width="15%">Tanggal Selesai</th>
                                                            <th>Nama Program</th>
                                                            <th>Komisi</th>
                                                            <th>Penanggung Jawab</th>
                                                            <th width="10%">Status</th>
                                                            <th width="10%" class="no-print">Aksi</th>
                                                        <?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($data_jadwal)): ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center py-4">
                                                                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                                                <p class="text-muted">Tidak ada data jadwal ditemukan</p>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($data_jadwal as $jadwal): ?>
                                                            <tr data-id="<?php echo $jadwal['id']; ?>">
                                                                <?php if ($jenis_jadwal == 'ibadah_minggu'): ?>
                                                                    <td>
                                                                        <i class="fas fa-calendar-alt calendar-icon me-2"></i>
                                                                        <?php echo date('d M Y', strtotime($jadwal['tanggal'])); ?>
                                                                    </td>
                                                                    <td><?php echo $jadwal['waktu']; ?></td>
                                                                    <td>
                                                                        <strong><?php echo htmlspecialchars($jadwal['pemimpin_ibadah']); ?></strong>
                                                                        <?php if ($jadwal['keterangan']): ?>
                                                                            <br><small class="text-muted"><?php echo htmlspecialchars($jadwal['keterangan']); ?></small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($jadwal['pemimpin_ibadah']); ?></td>
                                                                    <td>
                                                                        <?php 
                                                                        $petugas = json_decode($jadwal['petugas_musik'] ?? '[]', true);
                                                                        foreach ($petugas as $petugas_item): 
                                                                        ?>
                                                                            <span class="badge bg-secondary role-badge">
                                                                                <?php echo htmlspecialchars($petugas_item); ?>
                                                                            </span>
                                                                        <?php endforeach; ?>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge <?php echo getStatusBadgeClass($jadwal['status']); ?> badge-jadwal">
                                                                            <?php echo ucfirst($jadwal['status']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="no-print">
                                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                                onclick="editJadwal(<?php echo $jadwal['id']; ?>, 'ibadah_minggu')">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                                onclick="hapusJadwal(<?php echo $jadwal['id']; ?>, 'ibadah_minggu')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                <?php elseif ($jenis_jadwal == 'tukar_mimbar'): ?>
                                                                    <td>
                                                                        <i class="fas fa-calendar-alt calendar-icon me-2"></i>
                                                                        <?php echo date('d M Y', strtotime($jadwal['tanggal'])); ?>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($jadwal['gereja_pengirim']); ?></td>
                                                                    <td><?php echo htmlspecialchars($jadwal['gereja_penerima']); ?></td>
                                                                    <td><?php echo htmlspecialchars($jadwal['pemimpin_ibadah']); ?></td>
                                                                    <td><?php echo htmlspecialchars($jadwal['tema']); ?></td>
                                                                    <td>
                                                                        <span class="badge <?php echo getStatusBadgeClass($jadwal['status']); ?> badge-jadwal">
                                                                            <?php echo ucfirst($jadwal['status']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="no-print">
                                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                                onclick="editJadwal(<?php echo $jadwal['id']; ?>, 'tukar_mimbar')">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                                onclick="hapusJadwal(<?php echo $jadwal['id']; ?>, 'tukar_mimbar')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                <?php else: ?>
                                                                    <td>
                                                                        <i class="fas fa-calendar-alt calendar-icon me-2"></i>
                                                                        <?php echo date('d M Y', strtotime($jadwal['tanggal_mulai'])); ?>
                                                                    </td>
                                                                    <td>
                                                                        <i class="fas fa-calendar-alt calendar-icon me-2"></i>
                                                                        <?php echo date('d M Y', strtotime($jadwal['tanggal_selesai'])); ?>
                                                                    </td>
                                                                    <td>
                                                                        <strong><?php echo htmlspecialchars($jadwal['nama_program']); ?></strong>
                                                                        <?php if ($jadwal['deskripsi']): ?>
                                                                            <br><small class="text-muted"><?php echo htmlspecialchars($jadwal['deskripsi']); ?></small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-info badge-jadwal">
                                                                            <?php echo htmlspecialchars($jadwal['nama_komisi']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($jadwal['penanggung_jawab']); ?></td>
                                                                    <td>
                                                                        <span class="badge <?php echo getStatusBadgeClass($jadwal['status']); ?> badge-jadwal">
                                                                            <?php echo ucfirst($jadwal['status']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="no-print">
                                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                                onclick="editJadwal(<?php echo $jadwal['id']; ?>, 'program_kerja')">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                                onclick="hapusJadwal(<?php echo $jadwal['id']; ?>, 'program_kerja')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                <?php endif; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Calendar View -->
                                    <div class="tab-pane fade" id="calendar-view">
                                        <div id="calendar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Ibadah Minggu -->
    <div class="modal fade" id="modalTambahIbadah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Tambah Jadwal Ibadah Minggu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTambahIbadah" action="../controllers/JadwalController.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tambah_ibadah">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Ibadah *</label>
                                <input type="date" class="form-control" name="tanggal" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu *</label>
                                <input type="time" class="form-control" name="waktu" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pemimpin Ibadah *</label>
                                <input type="text" class="form-control" name="pemimpin_ibadah" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Petugas Musik</label>
                                <div id="petugasContainer">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="petugas_musik[]" placeholder="Nama petugas dan peran">
                                        <button type="button" class="btn btn-outline-secondary" onclick="tambahPetugas()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="aktif">Aktif</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Tukar Mimbar -->
    <div class="modal fade" id="modalTambahTukarMimbar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Tambah Jadwal Tukar Mimbar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTambahTukarMimbar" action="../controllers/JadwalController.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tambah_tukar_mimbar">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal *</label>
                                <input type="date" class="form-control" name="tanggal" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gereja Pengirim *</label>
                                <input type="text" class="form-control" name="gereja_pengirim" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gereja Penerima *</label>
                                <input type="text" class="form-control" name="gereja_penerima" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pemimpin Ibadah *</label>
                                <input type="text" class="form-control" name="pemimpin_ibadah" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tema *</label>
                                <input type="text" class="form-control" name="tema" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="terjadwal">Terjadwal</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Program Kerja -->
    <div class="modal fade" id="modalTambahProgram" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Tambah Program Kerja Komisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTambahProgram" action="../controllers/JadwalController.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tambah_program_kerja">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Program *</label>
                                <input type="text" class="form-control" name="nama_program" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Komisi *</label>
                                <select class="form-select" name="komisi_id" required>
                                    <option value="">Pilih Komisi</option>
                                    <?php foreach ($komisi_list as $komisi): ?>
                                        <option value="<?php echo $komisi['id']; ?>">
                                            <?php echo htmlspecialchars($komisi['nama_komisi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai *</label>
                                <input type="date" class="form-control" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai *</label>
                                <input type="date" class="form-control" name="tanggal_selesai" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Penanggung Jawab *</label>
                                <input type="text" class="form-control" name="penanggung_jawab" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Anggaran (Rp)</label>
                                <input type="number" class="form-control" name="anggaran" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="aktif">Aktif</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Export -->
    <div class="modal fade" id="modalExport" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Export Data</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formExport" action="../controllers/ExportController.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="jenis" value="<?php echo $jenis_jadwal; ?>">
                        <div class="mb-3">
                            <label class="form-label">Format Export</label>
                            <select class="form-select" name="format" required>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rentang Tanggal</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <input type="date" class="form-control" name="tanggal_mulai" placeholder="Dari">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <input type="date" class="form-control" name="tanggal_selesai" placeholder="Sampai">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_inactive" id="includeInactive">
                                <label class="form-check-label" for="includeInactive">
                                    Include data non-aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
        // Inisialisasi datepicker
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            locale: 'id'
        });

        // Fungsi untuk menambah petugas musik
        function tambahPetugas() {
            const container = document.getElementById('petugasContainer');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="petugas_musik[]"
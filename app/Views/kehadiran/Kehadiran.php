<?php
session_start();
require_once '../config/database.php';
require_once '../models/KehadiranModel.php';
require_once '../models/JadwalModel.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Cek hak akses - hanya admin dan ketua komisi yang bisa akses
$allowed_roles = ['admin', 'ketua_komisi', 'sekretaris'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: dashboard.php');
    exit();
}

// Inisialisasi model
$kehadiranModel = new KehadiranModel();
$jadwalModel = new JadwalModel();

// Ambil parameter filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$jenis_ibadah = isset($_GET['jenis_ibadah']) ? $_GET['jenis_ibadah'] : 'all';
$komisi_id = isset($_GET['komisi_id']) ? $_GET['komisi_id'] : 'all';

// Ambil data kehadiran berdasarkan filter
$data_kehadiran = $kehadiranModel->getKehadiranByFilter($bulan, $jenis_ibadah, $komisi_id);

// Ambil data untuk filter dropdown
$bulan_list = $kehadiranModel->getBulanList();
$jenis_ibadah_list = $jadwalModel->getJenisIbadah();
$komisi_list = $jadwalModel->getAllKomisi();

// Hitung statistik
$total_hadir = 0;
$total_izin = 0;
$total_alfa = 0;
$total_terlambat = 0;

foreach ($data_kehadiran as $record) {
    switch ($record['status_kehadiran']) {
        case 'hadir':
            $total_hadir++;
            if ($record['terlambat'] > 0) $total_terlambat++;
            break;
        case 'izin':
            $total_izin++;
            break;
        case 'alfa':
            $total_alfa++;
            break;
    }
}

$total_petugas = count($data_kehadiran);
$persen_hadir = $total_petugas > 0 ? round(($total_hadir / $total_petugas) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kehadiran Petugas Ibadah - Gereja <?php echo $_SESSION['gereja_nama']; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
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
        
        .card-header-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .stat-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card.hadir { border-left-color: var(--success-color); }
        .stat-card.izin { border-left-color: var(--info-color); }
        .stat-card.alfa { border-left-color: var(--danger-color); }
        .stat-card.terlambat { border-left-color: var(--warning-color); }
        
        .badge-kehadiran {
            font-size: 0.8em;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .badge-hadir { background-color: #d4edda; color: #155724; }
        .badge-izin { background-color: #d1ecf1; color: #0c5460; }
        .badge-alfa { background-color: #f8d7da; color: #721c24; }
        .badge-terlambat { background-color: #fff3cd; color: #856404; }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }
        
        .table th {
            background-color: var(--light-color);
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .filter-section {
            background-color: white;
            padding: 20px;
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
        
        .btn-export {
            background-color: var(--success-color);
            color: white;
        }
        
        .calendar-icon {
            color: var(--secondary-color);
        }
        
        .attendance-mark {
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .attendance-mark:hover {
            transform: scale(1.2);
        }
        
        .progress-bar-custom {
            height: 10px;
            border-radius: 5px;
        }
        
        .highlight-row {
            background-color: #fffde7 !important;
        }
        
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            height: calc(1.5em + 0.75rem + 2px);
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
                    <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['user_role']); ?></span>
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
                            <a class="nav-link" href="jadwalpetugasibadah.php?jenis=ibadah_minggu">
                                <i class="fas fa-calendar-alt me-2"></i> Jadwal Ibadah
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link active" href="kehadiran.php">
                                <i class="fas fa-clipboard-check me-2"></i> Kehadiran
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="laporan.php">
                                <i class="fas fa-chart-bar me-2"></i> Laporan
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="petugas.php">
                                <i class="fas fa-users me-2"></i> Data Petugas
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <div class="dropdown">
                                <button class="btn btn-custom w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-plus me-2"></i> Aksi Cepat
                                </button>
                                <ul class="dropdown-menu w-100">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalTambahKehadiran">
                                        <i class="fas fa-user-check me-2"></i> Input Kehadiran
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalImportKehadiran">
                                        <i class="fas fa-file-import me-2"></i> Import Data
                                    </a></li>
                                    <li><a class="dropdown-item" href="rekapitulasi.php">
                                        <i class="fas fa-file-alt me-2"></i> Rekapitulasi
                                    </a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                    
                    <!-- Filter Sidebar -->
                    <div class="mt-5">
                        <h6 class="text-muted">Filter Cepat</h6>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Bulan:</small>
                            <select class="form-select form-select-sm mb-3" id="sidebarBulan" onchange="updateFilter('bulan', this.value)">
                                <?php foreach ($bulan_list as $bln): ?>
                                    <option value="<?php echo $bln['value']; ?>" <?php echo $bulan == $bln['value'] ? 'selected' : ''; ?>>
                                        <?php echo $bln['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <small class="text-muted d-block mb-1">Jenis Ibadah:</small>
                            <select class="form-select form-select-sm mb-3" id="sidebarJenisIbadah" onchange="updateFilter('jenis_ibadah', this.value)">
                                <option value="all" <?php echo $jenis_ibadah == 'all' ? 'selected' : ''; ?>>Semua Jenis</option>
                                <?php foreach ($jenis_ibadah_list as $jenis): ?>
                                    <option value="<?php echo $jenis['id']; ?>" <?php echo $jenis_ibadah == $jenis['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($jenis['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <small class="text-muted d-block mb-1">Komisi:</small>
                            <select class="form-select form-select-sm" id="sidebarKomisi" onchange="updateFilter('komisi_id', this.value)">
                                <option value="all" <?php echo $komisi_id == 'all' ? 'selected' : ''; ?>>Semua Komisi</option>
                                <?php foreach ($komisi_list as $komisi): ?>
                                    <option value="<?php echo $komisi['id']; ?>" <?php echo $komisi_id == $komisi['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($komisi['nama_komisi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Kehadiran Petugas Ibadah</h1>
                        <p class="text-muted">Monitor dan kelola kehadiran petugas pelayanan</p>
                    </div>
                    <div class="no-print">
                        <button class="btn btn-export me-2" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button class="btn btn-custom" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Cetak
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card stat-card hadir">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle text-muted">Hadir</h6>
                                        <h3 class="card-title"><?php echo $total_hadir; ?></h3>
                                        <small><?php echo $persen_hadir; ?>% dari total</small>
                                    </div>
                                    <div class="display-4 text-success">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                                <div class="progress progress-bar-custom mt-2">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $persen_hadir; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card stat-card izin">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle text-muted">Izin</h6>
                                        <h3 class="card-title"><?php echo $total_izin; ?></h3>
                                        <small><?php echo $total_petugas > 0 ? round(($total_izin / $total_petugas) * 100, 2) : 0; ?>% dari total</small>
                                    </div>
                                    <div class="display-4 text-info">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                </div>
                                <div class="progress progress-bar-custom mt-2">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?php echo $total_petugas > 0 ? ($total_izin / $total_petugas) * 100 : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card stat-card alfa">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle text-muted">Alfa</h6>
                                        <h3 class="card-title"><?php echo $total_alfa; ?></h3>
                                        <small><?php echo $total_petugas > 0 ? round(($total_alfa / $total_petugas) * 100, 2) : 0; ?>% dari total</small>
                                    </div>
                                    <div class="display-4 text-danger">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                </div>
                                <div class="progress progress-bar-custom mt-2">
                                    <div class="progress-bar bg-danger" role="progressbar" 
                                         style="width: <?php echo $total_petugas > 0 ? ($total_alfa / $total_petugas) * 100 : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card stat-card terlambat">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle text-muted">Terlambat</h6>
                                        <h3 class="card-title"><?php echo $total_terlambat; ?></h3>
                                        <small><?php echo $total_hadir > 0 ? round(($total_terlambat / $total_hadir) * 100, 2) : 0; ?>% dari hadir</small>
                                    </div>
                                    <div class="display-4 text-warning">
                                        <i class="fas fa-running"></i>
                                    </div>
                                </div>
                                <div class="progress progress-bar-custom mt-2">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: <?php echo $total_hadir > 0 ? ($total_terlambat / $total_hadir) * 100 : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section no-print">
                    <h5 class="mb-3">Filter Data</h5>
                    <form method="GET" action="" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bulan</label>
                                <select class="form-select" name="bulan" id="bulanFilter">
                                    <?php foreach ($bulan_list as $bln): ?>
                                        <option value="<?php echo $bln['value']; ?>" <?php echo $bulan == $bln['value'] ? 'selected' : ''; ?>>
                                            <?php echo $bln['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jenis Ibadah</label>
                                <select class="form-select select2" name="jenis_ibadah" id="jenisIbadahFilter">
                                    <option value="all" <?php echo $jenis_ibadah == 'all' ? 'selected' : ''; ?>>Semua Jenis</option>
                                    <?php foreach ($jenis_ibadah_list as $jenis): ?>
                                        <option value="<?php echo $jenis['id']; ?>" <?php echo $jenis_ibadah == $jenis['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jenis['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Komisi</label>
                                <select class="form-select select2" name="komisi_id" id="komisiFilter">
                                    <option value="all" <?php echo $komisi_id == 'all' ? 'selected' : ''; ?>>Semua Komisi</option>
                                    <?php foreach ($komisi_list as $komisi): ?>
                                        <option value="<?php echo $komisi['id']; ?>" <?php echo $komisi_id == $komisi['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($komisi['nama_komisi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-custom w-100">
                                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Data Kehadiran Petugas</h5>
                                <span class="badge bg-light text-dark">
                                    Total: <?php echo $total_petugas; ?> Data
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($data_kehadiran)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data kehadiran ditemukan</p>
                                        <a href="#" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalTambahKehadiran">
                                            <i class="fas fa-plus me-1"></i> Tambah Data Kehadiran
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="tableKehadiran">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="15%">Tanggal</th>
                                                    <th width="20%">Petugas</th>
                                                    <th width="15%">Peran</th>
                                                    <th width="15%">Jenis Ibadah</th>
                                                    <th width="15%">Status</th>
                                                    <th width="10%">Waktu</th>
                                                    <th width="5%" class="no-print">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php foreach ($data_kehadiran as $kehadiran): ?>
                                                    <tr class="<?php echo $kehadiran['terlambat'] > 0 ? 'highlight-row' : ''; ?>">
                                                        <td><?php echo $no++; ?></td>
                                                        <td>
                                                            <i class="fas fa-calendar-alt calendar-icon me-2"></i>
                                                            <?php echo date('d M Y', strtotime($kehadiran['tanggal_ibadah'])); ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo $kehadiran['waktu_ibadah']; ?></small>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($kehadiran['nama_petugas']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($kehadiran['nama_komisi']); ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo htmlspecialchars($kehadiran['peran_pelayanan']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($kehadiran['jenis_ibadah']); ?></td>
                                                        <td>
                                                            <?php if ($kehadiran['status_kehadiran'] == 'hadir'): ?>
                                                                <span class="badge badge-kehadiran badge-hadir">
                                                                    <i class="fas fa-check me-1"></i> Hadir
                                                                    <?php if ($kehadiran['terlambat'] > 0): ?>
                                                                        <small>(+<?php echo $kehadiran['terlambat']; ?> menit)</small>
                                                                    <?php endif; ?>
                                                                </span>
                                                            <?php elseif ($kehadiran['status_kehadiran'] == 'izin'): ?>
                                                                <span class="badge badge-kehadiran badge-izin">
                                                                    <i class="fas fa-clock me-1"></i> Izin
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-kehadiran badge-alfa">
                                                                    <i class="fas fa-times me-1"></i> Alfa
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <small>
                                                                <?php if ($kehadiran['jam_hadir']): ?>
                                                                    <?php echo date('H:i', strtotime($kehadiran['jam_hadir'])); ?>
                                                                    <?php if ($kehadiran['jam_pulang']): ?>
                                                                        <br>-
                                                                        <?php echo date('H:i', strtotime($kehadiran['jam_pulang'])); ?>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </small>
                                                        </td>
                                                        <td class="no-print">
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" 
                                                                        onclick="editKehadiran(<?php echo $kehadiran['id']; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger" 
                                                                        onclick="hapusKehadiran(<?php echo $kehadiran['id']; ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom">
                                <h5 class="mb-0">Grafik Kehadiran Bulan <?php echo date('F Y', strtotime($bulan . '-01')); ?></h5>
                            </div>
                            <div class="card-body">
                                <canvas id="kehadiranChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom">
                                <h5 class="mb-0">Peringkat Komisi</h5>
                            </div>
                            <div class="card-body">
                                <div id="rankingKomisi">
                                    <?php
                                    // Hitung ranking komisi
                                    $ranking = $kehadiranModel->getRankingKomisi($bulan);
                                    if (!empty($ranking)):
                                        $rank = 1;
                                        foreach ($ranking as $komisi):
                                            $persentase = $komisi['total_petugas'] > 0 ? 
                                                round(($komisi['total_hadir'] / $komisi['total_petugas']) * 100, 1) : 0;
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <div>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">#<?php echo $rank++; ?></span>
                                                    <strong><?php echo htmlspecialchars($komisi['nama_komisi']); ?></strong>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo $komisi['total_hadir']; ?> dari <?php echo $komisi['total_petugas']; ?> petugas hadir
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <h5 class="mb-0"><?php echo $persentase; ?>%</h5>
                                                <small class="text-muted">Kehadiran</small>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <p class="text-muted text-center py-3">Belum ada data ranking</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kehadiran -->
    <div class="modal fade" id="modalTambahKehadiran" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Input Kehadiran Petugas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTambahKehadiran" action="../controllers/KehadiranController.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tambah_kehadiran">
                        
                        <!-- Pilih Jadwal Ibadah -->
                        <div class="mb-4">
                            <h6 class="mb-3">1. Pilih Jadwal Ibadah</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Ibadah *</label>
                                    <input type="date" class="form-control" name="tanggal_ibadah" id="tanggalIbadah" required 
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Ibadah *</label>
                                    <select class="form-select" name="jadwal_id" id="jenisIbadahSelect" required>
                                        <option value="">Pilih Jenis Ibadah</option>
                                        <?php foreach ($jenis_ibadah_list as $jenis): ?>
                                            <option value="<?php echo $jenis['id']; ?>">
                                                <?php echo htmlspecialchars($jenis['nama']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pilih Petugas -->
                        <div class="mb-4">
                            <h6 class="mb-3">2. Pilih Petugas</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Petugas *</label>
                                    <select class="form-select select2-modal" name="petugas_id" id="petugasSelect" required>
                                        <option value="">Cari petugas...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Peran Pelayanan *</label>
                                    <input type="text" class="form-control" name="peran_pelayanan" required 
                                           placeholder="Contoh: Pemimpin Pujian, Pengkotbah, dll">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Data Kehadiran -->
                        <div class="mb-4">
                            <h6 class="mb-3">3. Data Kehadiran</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status Kehadiran *</label>
                                    <select class="form-select" name="status_kehadiran" id="statusKehadiran" required>
                                        <option value="hadir">Hadir</option>
                                        <option value="izin">Izin</option>
                                        <option value="alfa">Alfa</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jam Hadir</label>
                                    <input type="time" class="form-control" name="jam_hadir" id="jamHadir">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jam Pulang</label>
                                    <input type="time" class="form-control" name="jam_pulang" id="jamPulang">
                                </div>
                            </div>
                            <div class="row" id="alasanIzin" style="display: none;">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Alasan Izin</label>
                                    <textarea class="form-control" name="alasan_izin" rows="2" placeholder="Isi alasan izin jika ada"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Keterangan Tambahan -->
                        <div class="mb-3">
                            <label class="form-label">Catatan / Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">Simpan Kehadiran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Kehadiran -->
    <div class="modal fade" id="modalImportKehadiran" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Import Data Kehadiran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formImportKehadiran" action="../controllers/KehadiranController.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="import_kehadiran">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Unduh template Excel terlebih dahulu, isi
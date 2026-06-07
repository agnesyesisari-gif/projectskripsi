<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/KehadiranModel.php';
require_once '../../models/JadwalModel.php';
require_once '../../models/PetugasModel.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: kehadiran.php');
    exit();
}

$kehadiran_id = $_GET['id'];

// Inisialisasi model
$kehadiranModel = new KehadiranModel();
$jadwalModel = new JadwalModel();
$petugasModel = new PetugasModel();

// Ambil data kehadiran
$kehadiran = $kehadiranModel->getKehadiranById($kehadiran_id);

// Cek apakah data ditemukan
if (!$kehadiran) {
    header('Location: kehadiran.php');
    exit();
}

// Cek hak akses
$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

// Jika user adalah petugas biasa, hanya bisa melihat data sendiri
if ($userRole == 'petugas' && $kehadiran['petugas_id'] != $userId) {
    header('Location: index.php');
    exit();
}

// Ambil data terkait
$petugas_data = $petugasModel->getPetugasById($kehadiran['petugas_id']);
$jadwal_data = $jadwalModel->getJadwalById($kehadiran['jadwal_id']);
$komisi_data = $petugasModel->getKomisiById($petugas_data['komisi_id']);

// Ambil riwayat kehadiran petugas
$riwayat_kehadiran = $kehadiranModel->getRiwayatKehadiranPetugas($kehadiran['petugas_id'], 10);

// Proses update kehadiran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek hak akses untuk edit
    $allowed_edit_roles = ['admin', 'ketua_komisi'];
    if (!in_array($userRole, $allowed_edit_roles) && $kehadiran['petugas_id'] != $userId) {
        header('Location: detail.php?id=' . $kehadiran_id);
        exit();
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_kehadiran') {
        $update_data = [
            'id' => $kehadiran_id,
            'status_kehadiran' => $_POST['status_kehadiran'] ?? 'hadir',
            'jam_hadir' => $_POST['jam_hadir'] ?? null,
            'jam_pulang' => $_POST['jam_pulang'] ?? null,
            'alasan_izin' => $_POST['alasan_izin'] ?? '',
            'keterangan' => $_POST['keterangan'] ?? '',
            'terlambat' => $_POST['terlambat'] ?? 0,
            'updated_by' => $_SESSION['user_id']
        ];
        
        $result = $kehadiranModel->updateKehadiran($update_data);
        
        if ($result) {
            // Refresh data
            $kehadiran = $kehadiranModel->getKehadiranById($kehadiran_id);
            $success_message = "Data kehadiran berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui data kehadiran.";
        }
    }
    
    // Tambah catatan
    elseif ($action == 'add_note') {
        $catatan_data = [
            'kehadiran_id' => $kehadiran_id,
            'user_id' => $_SESSION['user_id'],
            'catatan' => $_POST['catatan'] ?? '',
            'jenis' => $_POST['jenis_catatan'] ?? 'info'
        ];
        
        $result = $kehadiranModel->addCatatanKehadiran($catatan_data);
        
        if ($result) {
            $success_message = "Catatan berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan catatan.";
        }
    }
    
    // Verifikasi kehadiran
    elseif ($action == 'verify_kehadiran') {
        if (in_array($userRole, ['admin', 'ketua_komisi'])) {
            $result = $kehadiranModel->verifyKehadiran($kehadiran_id, $_SESSION['user_id']);
            
            if ($result) {
                $kehadiran = $kehadiranModel->getKehadiranById($kehadiran_id);
                $success_message = "Kehadiran telah diverifikasi!";
            } else {
                $error_message = "Gagal memverifikasi kehadiran.";
            }
        }
    }
}

// Ambil catatan kehadiran
$catatan_kehadiran = $kehadiranModel->getCatatanKehadiran($kehadiran_id);

// Format waktu
function formatWaktu($waktu) {
    if (empty($waktu) || $waktu == '00:00:00') {
        return '-';
    }
    return date('H:i', strtotime($waktu));
}

// Format tanggal Indonesia
function formatTanggal($tanggal) {
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    $timestamp = strtotime($tanggal);
    $hari_ini = $hari[date('w', $timestamp)];
    $tanggal_ini = date('j', $timestamp);
    $bulan_ini = $bulan[date('n', $timestamp) - 1];
    $tahun_ini = date('Y', $timestamp);
    
    return "$hari_ini, $tanggal_ini $bulan_ini $tahun_ini";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kehadiran - Gereja <?php echo $_SESSION['gereja_nama']; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #1a252f);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .attendance-status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-hadir { background-color: #d4edda; color: #155724; }
        .status-izin { background-color: #d1ecf1; color: #0c5460; }
        .status-alfa { background-color: #f8d7da; color: #721c24; }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto;
        }
        
        .detail-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        
        .detail-value {
            color: #6c757d;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            border: 2px solid white;
        }
        
        .timeline-item.success::before {
            background-color: var(--success-color);
        }
        
        .timeline-item.warning::before {
            background-color: var(--warning-color);
        }
        
        .timeline-item.danger::before {
            background-color: var(--danger-color);
        }
        
        .timeline-item.info::before {
            background-color: var(--info-color);
        }
        
        .note-card {
            border-left: 4px solid;
            border-radius: 8px;
        }
        
        .note-info { border-left-color: var(--info-color); }
        .note-warning { border-left-color: var(--warning-color); }
        .note-success { border-left-color: var(--success-color); }
        .note-danger { border-left-color: var(--danger-color); }
        
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
        
        .btn-verify {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-print {
            background-color: var(--warning-color);
            color: white;
        }
        
        .verified-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .history-table {
            font-size: 0.9rem;
        }
        
        .history-table .badge {
            font-size: 0.75rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .page-header {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin-bottom: 20px !important;
            }
            
            .card-custom {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-church me-2"></i>
                Gereja <?php echo $_SESSION['gereja_nama']; ?>
            </a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3 d-none d-md-block">
                    <i class="fas fa-user me-1"></i> <?php echo $_SESSION['user_nama']; ?>
                </span>
                <a href="kehadiran.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb" style="background-color: transparent; padding: 0;">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="kehadiran.php" class="text-white">Kehadiran</a></li>
                            <li class="breadcrumb-item active text-white">Detail Kehadiran</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">Detail Kehadiran</h1>
                    <p class="mb-0 opacity-75">ID: <?php echo str_pad($kehadiran_id, 6, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="action-buttons no-print">
                        <button class="btn btn-print" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Cetak
                        </button>
                        <?php if (in_array($userRole, ['admin', 'ketua_komisi']) || $kehadiran['petugas_id'] == $userId): ?>
                            <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalEditKehadiran">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                        <?php endif; ?>
                        <?php if (in_array($userRole, ['admin', 'ketua_komisi']) && $kehadiran['verified'] == 0): ?>
                            <button class="btn btn-verify" onclick="verifyAttendance()">
                                <i class="fas fa-check me-1"></i> Verifikasi
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: Data Kehadiran & Petugas -->
            <div class="col-lg-8">
                <!-- Status & Info Card -->
                <div class="card card-custom mb-4">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Kehadiran</h5>
                        <?php if ($kehadiran['verified'] == 1): ?>
                            <span class="verified-badge">
                                <i class="fas fa-check-circle me-1"></i> Telah Diverifikasi
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning">Belum Diverifikasi</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <span class="attendance-status-badge <?php echo 'status-' . $kehadiran['status_kehadiran']; ?>">
                                            <i class="fas <?php 
                                                echo $kehadiran['status_kehadiran'] == 'hadir' ? 'fa-user-check' : 
                                                    ($kehadiran['status_kehadiran'] == 'izin' ? 'fa-user-clock' : 'fa-user-times');
                                            ?> me-2"></i>
                                            <?php echo strtoupper($kehadiran['status_kehadiran']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="col-md-6 detail-item">
                                        <div class="detail-label">Tanggal Ibadah</div>
                                        <div class="detail-value">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <?php echo formatTanggal($kehadiran['tanggal_ibadah']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 detail-item">
                                        <div class="detail-label">Waktu Ibadah</div>
                                        <div class="detail-value">
                                            <i class="far fa-clock me-2"></i>
                                            <?php echo !empty($jadwal_data['waktu']) ? date('H:i', strtotime($jadwal_data['waktu'])) : '-'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 detail-item">
                                        <div class="detail-label">Jenis Ibadah</div>
                                        <div class="detail-value">
                                            <i class="fas fa-pray me-2"></i>
                                            <?php echo !empty($jadwal_data['jenis_ibadah']) ? htmlspecialchars($jadwal_data['jenis_ibadah']) : '-'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 detail-item">
                                        <div class="detail-label">Tempat</div>
                                        <div class="detail-value">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <?php echo !empty($jadwal_data['tempat']) ? htmlspecialchars($jadwal_data['tempat']) : '-'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 detail-item">
                                        <div class="detail-label">Jam Hadir</div>
                                        <div class="detail-value">
                                            <i class="fas fa-sign-in-alt me-2"></i>
                                            <?php echo formatWaktu($kehadiran['jam_hadir']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 detail-item">
                                        <div class="detail-label">Jam Pulang</div>
                                        <div class="detail-value">
                                            <i class="fas fa-sign-out-alt me-2"></i>
                                            <?php echo formatWaktu($kehadiran['jam_pulang']); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($kehadiran['terlambat'] > 0): ?>
                                        <div class="col-12 detail-item">
                                            <div class="detail-label">Keterlambatan</div>
                                            <div class="detail-value text-warning">
                                                <i class="fas fa-running me-2"></i>
                                                <?php echo $kehadiran['terlambat']; ?> menit
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($kehadiran['alasan_izin'])): ?>
                                        <div class="col-12 detail-item">
                                            <div class="detail-label">Alasan Izin</div>
                                            <div class="detail-value">
                                                <i class="fas fa-sticky-note me-2"></i>
                                                <?php echo htmlspecialchars($kehadiran['alasan_izin']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($kehadiran['keterangan'])): ?>
                                        <div class="col-12 detail-item">
                                            <div class="detail-label">Keterangan</div>
                                            <div class="detail-value">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <?php echo htmlspecialchars($kehadiran['keterangan']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center mb-3">
                                    <div class="profile-avatar mb-3">
                                        <?php echo strtoupper(substr($petugas_data['nama'], 0, 2)); ?>
                                    </div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($petugas_data['nama']); ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-id-card me-1"></i>
                                        <?php echo htmlspecialchars($petugas_data['no_induk'] ?? '-'); ?>
                                    </p>
                                    <span class="badge bg-info">
                                        <?php echo !empty($komisi_data['nama_komisi']) ? htmlspecialchars($komisi_data['nama_komisi']) : '-'; ?>
                                    </span>
                                </div>
                                
                                <div class="timeline">
                                    <div class="timeline-item success">
                                        <small class="text-muted">Dibuat</small>
                                        <div class="small">
                                            <?php echo date('d/m/Y H:i', strtotime($kehadiran['created_at'])); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($kehadiran['updated_at'] != $kehadiran['created_at']): ?>
                                        <div class="timeline-item info">
                                            <small class="text-muted">Diperbarui</small>
                                            <div class="small">
                                                <?php echo date('d/m/Y H:i', strtotime($kehadiran['updated_at'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($kehadiran['verified'] == 1 && !empty($kehadiran['verified_by'])): ?>
                                        <div class="timeline-item success">
                                            <small class="text-muted">Diverifikasi</small>
                                            <div class="small">
                                                <?php echo date('d/m/Y H:i', strtotime($kehadiran['verified_at'])); ?>
                                                <br>
                                                <small>Oleh: <?php echo htmlspecialchars($kehadiran['verified_by_name']); ?></small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Riwayat Kehadiran Petugas -->
                <div class="card card-custom">
                    <div class="card-header card-header-custom">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Riwayat Kehadiran Petugas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($riwayat_kehadiran)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm history-table">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jenis Ibadah</th>
                                            <th>Peran</th>
                                            <th>Status</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($riwayat_kehadiran as $riwayat): ?>
                                            <tr class="<?php echo $riwayat['id'] == $kehadiran_id ? 'table-active' : ''; ?>">
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($riwayat['tanggal_ibadah'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($riwayat['jenis_ibadah']); ?></td>
                                                <td><?php echo htmlspecialchars($riwayat['peran_pelayanan']); ?></td>
                                                <td>
                                                    <?php if ($riwayat['status_kehadiran'] == 'hadir'): ?>
                                                        <span class="badge bg-success">Hadir</span>
                                                    <?php elseif ($riwayat['status_kehadiran'] == 'izin'): ?>
                                                        <span class="badge bg-info">Izin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Alfa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($riwayat['jam_hadir']): ?>
                                                        <?php echo date('H:i', strtotime($riwayat['jam_hadir'])); ?>
                                                        <?php if ($riwayat['jam_pulang']): ?>
                                                            - <?php echo date('H:i', strtotime($riwayat['jam_pulang'])); ?>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="riwayat.php?petugas_id=<?php echo $kehadiran['petugas_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i> Lihat Semua Riwayat
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Belum ada riwayat kehadiran lainnya</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

                <!-- Catatan Kehadiran -->
                <div class="card card-custom">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            Catatan
                        </h5>
                        <button class="btn btn-sm btn-custom" data-bs-toggle="modal" data-bs-target="#modalAddNote">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($catatan_kehadiran)): ?>
                            <div class="timeline">
                                <?php foreach ($catatan_kehadiran as $catatan): ?>
                                    <div class="timeline-item <?php echo $catatan['jenis']; ?> mb-3">
                                        <div class="note-card p-3 <?php echo 'note-' . $catatan['jenis']; ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong><?php echo htmlspecialchars($catatan['user_nama']); ?></strong>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($catatan['created_at'])); ?>
                                                </small>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($catatan['catatan'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Belum ada catatan</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kehadiran -->
    <div class="modal fade" id="modalEditKehadiran" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Edit Data Kehadiran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_kehadiran">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Kehadiran *</label>
                                <select class="form-select" name="status_kehadiran" id="editStatusKehadiran" required>
                                    <option value="hadir" <?php echo $kehadiran['status_kehadiran'] == 'hadir' ? 'selected' : ''; ?>>Hadir</option>
                                    <option value="izin" <?php echo $kehadiran['status_kehadiran'] == 'izin' ? 'selected' : ''; ?>>Izin</option>
                                    <option value="alfa" <?php echo $kehadiran['status_kehadiran'] == 'alfa' ? 'selected' : ''; ?>>Alfa</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterlambatan (menit)</label>
                                <input type="number" class="form-control" name="terlambat" 
                                       value="<?php echo $kehadiran['terlambat'] ?? 0; ?>" min="0">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Hadir</label>
                                <input type="time" class="form-control" name="jam_hadir" 
                                       value="<?php echo !empty($kehadiran['jam_hadir']) ? date('H:i', strtotime($kehadiran['jam_hadir'])) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Pulang</label>
                                <input type="time" class="form-control" name="jam_pulang" 
                                       value="<?php echo !empty($kehadiran['jam_pulang']) ? date('H:i', strtotime($kehadiran['jam_pulang'])) : ''; ?>">
                            </div>
                            
                            <div class="col-md-12 mb-3" id="alasanIzinField" style="display: <?php echo $kehadiran['status_kehadiran'] == 'izin' ? 'block' : 'none'; ?>;">
                                <label class="form-label">Alasan Izin</label>
                                <textarea class="form-control" name="alasan_izin" rows="3"><?php echo htmlspecialchars($kehadiran['alasan_izin'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan Tambahan</label>
                                <textarea class="form-control" name="keterangan" rows="3"><?php echo htmlspecialchars($kehadiran['keterangan'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Catatan -->
    <div class="modal fade" id="modalAddNote" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title">Tambah Catatan</
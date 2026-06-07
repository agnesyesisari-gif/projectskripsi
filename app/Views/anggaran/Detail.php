<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/AnggaranModel.php';
require_once '../../models/KomisiModel.php';
require_once '../../models/ProgramKerjaModel.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Cek hak akses
$allowed_roles = ['admin', 'ketua_komisi', 'bendahara', 'sekretaris'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: ../kehadiran/index.php');
    exit();
}

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$anggaran_id = $_GET['id'];
$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

// Inisialisasi model
$anggaranModel = new AnggaranModel();
$komisiModel = new KomisiModel();

// Ambil data anggaran
$anggaran = $anggaranModel->getAnggaranById($anggaran_id);

// Cek apakah data ditemukan
if (!$anggaran) {
    header('Location: index.php');
    exit();
}

// Cek hak akses untuk melihat
if ($userRole == 'ketua_komisi') {
    $userKomisi = $komisiModel->getKomisiByKetua($userId);
    if (!$userKomisi || $anggaran['komisi_id'] != $userKomisi['id']) {
        header('Location: index.php');
        exit();
    }
}

// Ambil data terkait
$items = $anggaranModel->getAnggaranItems($anggaran_id);
$dokumen = $anggaranModel->getAnggaranDokumen($anggaran_id);
$logs = $anggaranModel->getAnggaranLog($anggaran_id);
$timeline = $anggaranModel->getAnggaranTimeline($anggaran_id);

// Hitung total item
$total_items = count($items);
$total_rencana = array_sum(array_column($items, 'nominal_rencana'));
$total_realisasi = array_sum(array_column($items, 'nominal_realisasi'));
$persentase_realisasi = $total_rencana > 0 ? round(($total_realisasi / $total_rencana) * 100, 1) : 0;

// Proses form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Update status
    if ($action == 'update_status') {
        if (in_array($userRole, ['admin', 'bendahara'])) {
            $new_status = $_POST['status'] ?? '';
            $catatan = $_POST['catatan'] ?? '';
            
            if ($new_status == 'disetujui') {
                $nominal_disetujui = str_replace(['.', ','], '', $_POST['nominal_disetujui'] ?? '0');
                $result = $anggaranModel->verifyAnggaran($anggaran_id, $userId, $nominal_disetujui);
                $message = 'Anggaran berhasil disetujui!';
            } elseif ($new_status == 'ditolak') {
                $result = $anggaranModel->rejectAnggaran($anggaran_id, $userId, $catatan);
                $message = 'Anggaran berhasil ditolak!';
            } else {
                $result = $anggaranModel->updateAnggaranStatus($anggaran_id, $new_status, $userId, $catatan);
                $message = 'Status berhasil diperbarui!';
            }
            
            if ($result) {
                $success_message = $message;
                // Refresh data
                $anggaran = $anggaranModel->getAnggaranById($anggaran_id);
            } else {
                $error_message = 'Gagal memperbarui status!';
            }
        }
    }
    
    // Tambah realisasi
    elseif ($action == 'add_realisasi') {
        if ($userRole == 'ketua_komisi' || $userRole == 'admin') {
            $item_id = $_POST['item_id'] ?? '';
            $nominal_realisasi = str_replace(['.', ','], '', $_POST['nominal_realisasi'] ?? '0');
            $tanggal_realisasi = $_POST['tanggal_realisasi'] ?? '';
            $catatan = $_POST['catatan'] ?? '';
            
            if ($item_id) {
                $item_data = [
                    'nominal_realisasi' => $nominal_realisasi,
                    'status' => 'selesai',
                    'tanggal' => $tanggal_realisasi,
                    'catatan' => $catatan,
                    'updated_by' => $userId
                ];
                $result = $anggaranModel->updateAnggaranItem($item_id, $item_data);
                
                if ($result) {
                    $success_message = 'Realisasi berhasil ditambahkan!';
                    // Update total realisasi anggaran
                    $new_total_realisasi = $total_realisasi + $nominal_realisasi;
                    $anggaranModel->updateRealisasiAnggaran($anggaran_id, $new_total_realisasi, $userId);
                    // Refresh data
                    $items = $anggaranModel->getAnggaranItems($anggaran_id);
                    $anggaran = $anggaranModel->getAnggaranById($anggaran_id);
                } else {
                    $error_message = 'Gagal menambahkan realisasi!';
                }
            }
        }
    }
    
    // Tambah item
    elseif ($action == 'add_item') {
        if (in_array($userRole, ['admin', 'ketua_komisi'])) {
            $item_data = [
                'anggaran_id' => $anggaran_id,
                'nama_item' => $_POST['nama_item'] ?? '',
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'nominal_rencana' => str_replace(['.', ','], '', $_POST['nominal_rencana'] ?? '0'),
                'tanggal' => $_POST['tanggal_item'] ?? null,
                'created_by' => $userId
            ];
            
            $result = $anggaranModel->addAnggaranItem($item_data);
            
            if ($result) {
                $success_message = 'Item berhasil ditambahkan!';
                // Refresh data
                $items = $anggaranModel->getAnggaranItems($anggaran_id);
            } else {
                $error_message = 'Gagal menambahkan item!';
            }
        }
    }
    
    // Upload dokumen
    elseif ($action == 'upload_dokumen') {
        if (in_array($userRole, ['admin', 'ketua_komisi'])) {
            if (isset($_FILES['dokumen_file']) && $_FILES['dokumen_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/anggaran/' . $anggaran_id . '/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $filename = time() . '_' . basename($_FILES['dokumen_file']['name']);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['dokumen_file']['tmp_name'], $target_file)) {
                    $dokumen_data = [
                        'anggaran_id' => $anggaran_id,
                        'nama_dokumen' => $_POST['nama_dokumen'] ?? 'Dokumen',
                        'jenis_dokumen' => $_POST['jenis_dokumen'] ?? 'lainnya',
                        'file_path' => $target_file,
                        'deskripsi' => $_POST['deskripsi_dokumen'] ?? '',
                        'uploaded_by' => $userId
                    ];
                    
                    $result = $anggaranModel->addAnggaranDokumen($dokumen_data);
                    
                    if ($result) {
                        $success_message = 'Dokumen berhasil diupload!';
                        // Refresh data
                        $dokumen = $anggaranModel->getAnggaranDokumen($anggaran_id);
                    } else {
                        $error_message = 'Gagal menyimpan data dokumen!';
                    }
                } else {
                    $error_message = 'Gagal upload file!';
                }
            } else {
                $error_message = 'File tidak ditemukan atau error!';
            }
        }
    }
}

// Format currency helper
function formatCurrency($amount) {
    if ($amount == 0) return 'Rp 0';
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format persentase
function formatPercentage($value) {
    return round($value, 1) . '%';
}

// Format tanggal Indonesia
function formatTanggal($tanggal) {
    if (empty($tanggal)) return '-';
    
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    $timestamp = strtotime($tanggal);
    $hari_ini = $hari[date('w', $timestamp)];
    $tanggal_ini = date('j', $timestamp);
    $bulan_ini = $bulan[date('n', $timestamp) - 1];
    $tahun_ini = date('Y', $timestamp);
    
    return "$hari_ini, $tanggal_ini $bulan_ini $tahun_ini";
}

// Get status badge class
function getStatusBadgeClass($status) {
    $classes = [
        'draft' => 'badge-draft',
        'diajukan' => 'badge-diajukan',
        'disetujui' => 'badge-disetujui',
        'direalisasi' => 'badge-direalisasi',
        'direalisasi_sebagian' => 'badge-direalisasi_sebagian',
        'ditolak' => 'badge-ditolak'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

// Get status text
function getStatusText($status) {
    $texts = [
        'draft' => 'Draft',
        'diajukan' => 'Diajukan',
        'disetujui' => 'Disetujui',
        'direalisasi' => 'Direalisasi',
        'direalisasi_sebagian' => 'Direalisasi Sebagian',
        'ditolak' => 'Ditolak'
    ];
    return $texts[$status] ?? $status;
}

// Get action buttons based on status and role
function getActionButtons($anggaran, $userRole) {
    $buttons = [];
    
    if ($anggaran['status'] == 'draft') {
        if (in_array($userRole, ['admin', 'ketua_komisi'])) {
            $buttons[] = ['type' => 'edit', 'label' => 'Edit', 'class' => 'btn-primary'];
            $buttons[] = ['type' => 'submit', 'label' => 'Ajukan', 'class' => 'btn-success'];
        }
    }
    
    if ($anggaran['status'] == 'diajukan') {
        if (in_array($userRole, ['admin', 'bendahara'])) {
            $buttons[] = ['type' => 'approve', 'label' => 'Setujui', 'class' => 'btn-success'];
            $buttons[] = ['type' => 'reject', 'label' => 'Tolak', 'class' => 'btn-danger'];
        }
        if (in_array($userRole, ['admin', 'ketua_komisi'])) {
            $buttons[] = ['type' => 'edit', 'label' => 'Edit', 'class' => 'btn-primary'];
        }
    }
    
    if ($anggaran['status'] == 'disetujui' || $anggaran['status'] == 'direalisasi_sebagian') {
        if (in_array($userRole, ['admin', 'ketua_komisi'])) {
            $buttons[] = ['type' => 'add_realisasi', 'label' => 'Tambah Realisasi', 'class' => 'btn-success'];
            $buttons[] = ['type' => 'add_item', 'label' => 'Tambah Item', 'class' => 'btn-primary'];
        }
    }
    
    if (in_array($userRole, ['admin'])) {
        $buttons[] = ['type' => 'delete', 'label' => 'Hapus', 'class' => 'btn-danger'];
    }
    
    return $buttons;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Anggaran - Gereja <?php echo $_SESSION['gereja_nama']; ?></title>
    
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
            --purple-color: #8e44ad;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), #1a252f);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .page-header {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)),
                        url('../../assets/images/budget-detail.jpg') center/cover;
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            position: relative;
        }
        
        .page-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }
        
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
            border-bottom: none;
            position: relative;
        }
        
        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(255,255,255,0.3);
        }
        
        .budget-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .budget-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .budget-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .progress-budget {
            height: 15px;
            border-radius: 10px;
            background-color: #e9ecef;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-budget .progress-bar {
            border-radius: 10px;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .badge-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .badge-draft { background-color: #e9ecef; color: #495057; }
        .badge-diajukan { background-color: #cff4fc; color: #055160; }
        .badge-disetujui { background-color: #d1e7dd; color: #0a3622; }
        .badge-direalisasi { background-color: #d4edda; color: #155724; }
        .badge-direalisasi_sebagian { background-color: #fff3cd; color: #664d03; }
        .badge-ditolak { background-color: #f8d7da; color: #721c24; }
        
        .verified-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
        }
        
        .detail-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #495057;
            min-width: 150px;
        }
        
        .detail-value {
            color: #212529;
            text-align: right;
            flex: 1;
        }
        
        .item-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .item-card:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .item-card.completed {
            border-left: 4px solid var(--success-color);
            background-color: #f8fff9;
        }
        
        .item-card.partial {
            border-left: 4px solid var(--warning-color);
            background-color: #fffdf2;
        }
        
        .item-card.pending {
            border-left: 4px solid #e9ecef;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .item-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .item-amount {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .item-progress {
            margin-top: 10px;
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
        
        .btn-custom {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .btn-approve {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
        }
        
        .btn-reject {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
        }
        
        .btn-print {
            background: linear-gradient(135deg, var(--warning-color), #f1c40f);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .dokumen-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .dokumen-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dokumen-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .currency-badge {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px 15px;
            font-weight: 500;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="bg-white rounded-circle p-1 me-2">
                    <i class="fas fa-coins text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold">Gereja <?php echo $_SESSION['gereja_nama']; ?></div>
                    <small class="opacity-75">Detail Anggaran</small>
                </div>
            </a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3 d-none d-md-block">
                    <i class="fas fa-user me-1"></i> <?php echo $_SESSION['user_nama']; ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
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
                            <li class="breadcrumb-item"><a href="anggaran.php" class="text-white">Anggaran</a></li>
                            <li class="breadcrumb-item active text-white">Detail Anggaran</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2"><?php echo htmlspecialchars($anggaran['nama_anggaran']); ?></h1>
                    <p class="mb-0 opacity-75">ID: <?php echo str_pad($anggaran_id, 6, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="action-buttons no-print">
                        <button class="btn btn-print" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Cetak
                        </button>
                        <?php 
                            $action_buttons = getActionButtons($anggaran, $userRole);
                            foreach ($action_buttons as $button):
                                if ($button['type'] == 'edit'): ?>
                                    <a href="edit.php?id=<?php echo $anggaran_id; ?>" class="btn btn-custom">
                                        <i class="fas fa-edit me-1"></i> <?php echo $button['label']; ?>
                                    </a>
                                <?php elseif ($button['type'] == 'submit'): ?>
                                    <button class="btn btn-custom btn-success" data-bs-toggle="modal" data-bs-target="#modalSubmit">
                                        <i class="fas fa-paper-plane me-1"></i> <?php echo $button['label']; ?>
                                    </button>
                                <?php elseif ($button['type'] == 'approve'): ?>
                                    <button class="btn btn-custom btn-approve" data-bs-toggle="modal" data-bs-target="#modalApprove">
                                        <i class="fas fa-check me-1"></i> <?php echo $button['label']; ?>
                                    </button>
                                <?php elseif ($button['type'] == 'reject'): ?>
                                    <button class="btn btn-custom btn-reject" data-bs-toggle="modal" data-bs-target="#modalReject">
                                        <i class="fas fa-times me-1"></i> <?php echo $button['label']; ?>
                                    </button>
                                <?php elseif ($button['type'] == 'add_realisasi'): ?>
                                    <button class="btn btn-custom btn-success" data-bs-toggle="modal" data-bs-target="#modalRealisasi">
                                        <i class="fas fa-money-bill-wave me-1"></i> <?php echo $button['label']; ?>
                                    </button>
                                <?php elseif ($button['type'] == 'add_item'): ?>
                                    <button class="btn btn-custom btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddItem">
                                        <i class="fas fa-plus me-1"></i> <?php echo $button['label']; ?>
                                    </button>
                                <?php elseif ($button['type'] == 'delete'): ?>
                                    <button class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="fas fa-trash me-1"></i> <?php echo $button['label']; ?>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Berhasil!</h5>
                        <p class="mb-0"><?php echo $success_message; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Terjadi Kesalahan</h5>
                        <p class="mb-0"><?php echo $error_message; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: Main Information -->
            <div class="col-lg-8">
                <!-- Budget Summary Card -->
                <div class="budget-summary">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="budget-number">
                                <?php echo formatCurrency($anggaran['nominal_disetujui'] > 0 ? $anggaran['nominal_disetujui'] : $anggaran['nominal_diajukan']); ?>
                            </div>
                            <div class="budget-label">
                                <?php echo $anggaran['status'] == 'disetujui' ? 'Disetujui' : 'Diajukan'; ?>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="budget-label">Realisasi</span>
                                <span class="budget-label"><?php echo formatPercentage($persentase_realisasi); ?></span>
                            </div>
                            <div class="progress-budget">
                                <div class="progress-bar bg-<?php echo $persentase_realisasi > 80 ? 'success' : 
                                                            ($persentase_realisasi > 50 ? 'warning' : 'danger'); ?>" 
                                     style="width: <?php echo $persentase_realisasi; ?>%">
                                    <?php echo $persentase_realisasi; ?>%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">
                                    <?php echo formatCurrency($total_realisasi); ?> terealisasi
                                </small>
                                <small class="text-muted">
                                    dari <?php echo formatCurrency($total_rencana); ?> rencana
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Info Card -->
                <div class="card card-custom mb-4">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Anggaran</h5>
                        <span class="badge-status <?php echo getStatusBadgeClass($anggaran['status']); ?>">
                            <?php echo getStatusText($anggaran['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <span class="detail-label">Komisi</span>
                                    <span class="detail-value">
                                        <?php echo htmlspecialchars($anggaran['nama_komisi']); ?>
                                    </span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Tahun</span>
                                    <span class="detail-value"><?php echo $anggaran['tahun']; ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Periode</span>
                                    <span class="detail-value">
                                        <?php echo formatTanggal($anggaran['tanggal_mulai']); ?> - 
                                        <?php echo formatTanggal($anggaran['tanggal_selesai']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <span class="detail-label">Diajukan</span>
                                    <span class="detail-value"><?php echo formatCurrency($anggaran['nominal_diajukan']); ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Disetujui</span>
                                    <span class="detail-value"><?php echo formatCurrency($anggaran['nominal_disetujui']); ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Realisasi</span>
                                    <span class="detail-value"><?php echo formatCurrency($anggaran['nominal_realisasi']); ?></span>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <hr>
                                <div class="mb-3">
                                    <h6>Deskripsi</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($anggaran['deskripsi'])); ?></p>
                                </div>
                                
                                <?php if (!empty($anggaran['catatan'])): ?>
                                    <div class="mb-3">
                                        <h6>Catatan</h6>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($anggaran['catatan'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Dibuat oleh <?php echo htmlspecialchars($anggaran['created_by_name']); ?>
                                            <br>
                                            <?php echo date('d/m/Y H:i', strtotime($anggaran['created_at'])); ?>
                                        </small>
                                    </div>
                                    
                                    <?php if ($anggaran['verified_by']): ?>
                                        <div class="text-end">
                                            <div class="verified-badge">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Diverifikasi oleh <?php echo htmlspecialchars($anggaran['verified_by_name']); ?>
                                                <br>
                                                <small><?php echo date('d/m/Y H:i', strtotime($anggaran['verified_at'])); ?></small>
<?php
// app/views/anggaran/approval.php

session_start();
require_once '../app/models/Anggaran.php';
require_once '../app/models/User.php';
require_once '../app/models/Komisi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Cek role (hanya admin, bendahara, atau ketua komisi)
$allowedRoles = ['admin', 'bendahara', 'ketua_komisi'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: unauthorized.php');
    exit();
}

$anggaranModel = new Anggaran();
$userModel = new User();
$komisiModel = new Komisi();

// Get data berdasarkan role
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$anggaranData = [];

if ($userRole == 'admin' || $userRole == 'bendahara') {
    // Admin dan bendahara bisa lihat semua yang perlu approval
    $anggaranData = $anggaranModel->getAnggaranByStatus('pending');
} else if ($userRole == 'ketua_komisi') {
    // Ketua komisi hanya bisa lihat anggaran dari komisinya
    $komisiUser = $userModel->getKomisiByUserId($userId);
    if ($komisiUser) {
        $anggaranData = $anggaranModel->getAnggaranByKomisiAndStatus($komisiUser->id_komisi, 'pending');
    }
}

// Handle approval action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        $anggaranId = $_POST['anggaran_id'];
        $catatan = $_POST['catatan'] ?? '';
        $action = isset($_POST['approve']) ? 'disetujui' : 'ditolak';
        
        // Update status anggaran
        $anggaranModel->updateStatusAnggaran($anggaranId, $action);
        
        // Log approval
        $logData = [
            'id_anggaran' => $anggaranId,
            'id_user' => $userId,
            'action' => $action,
            'catatan' => $catatan,
            'tanggal' => date('Y-m-d H:i:s')
        ];
        $anggaranModel->logApproval($logData);
        
        // Send notification
        if ($action == 'disetujui') {
            $message = "Anggaran Anda telah disetujui";
        } else {
            $message = "Anggaran Anda ditolak dengan catatan: " . $catatan;
        }
        
        $_SESSION['success_message'] = "Anggaran berhasil " . $action;
        header('Location: approval.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Anggaran - Gereja <?php echo NAMA_GEREJA; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .approval-card {
            border-left: 5px solid #0d6efd;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .approval-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .approval-card.pending {
            border-left-color: #ffc107;
        }
        .approval-card.approved {
            border-left-color: #198754;
        }
        .approval-card.rejected {
            border-left-color: #dc3545;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 5px 10px;
        }
        .amount {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'partials/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'partials/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-check-circle text-primary"></i>
                        Approval Anggaran
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <a href="anggaran.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Kembali ke Anggaran
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="all" <?php echo ($_GET['status'] ?? '') == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Menunggu Approval</option>
                                    <option value="disetujui" <?php echo ($_GET['status'] ?? '') == 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="ditolak" <?php echo ($_GET['status'] ?? '') == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_komisi" class="form-label">Komisi</label>
                                <select class="form-select" id="filter_komisi" name="komisi">
                                    <option value="all">Semua Komisi</option>
                                    <?php
                                    $komisiList = $komisiModel->getAllKomisi();
                                    foreach ($komisiList as $komisi):
                                    ?>
                                        <option value="<?php echo $komisi->id; ?>"
                                            <?php echo ($_GET['komisi'] ?? '') == $komisi->id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($komisi->nama_komisi); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_tanggal" class="form-label">Periode</label>
                                <input type="month" class="form-control" id="filter_tanggal" name="periode" 
                                       value="<?php echo $_GET['periode'] ?? date('Y-m'); ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="approval.php" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Approval List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clipboard-list"></i>
                                    Daftar Anggaran Menunggu Persetujuan
                                    <span class="badge bg-light text-dark ms-2"><?php echo count($anggaranData); ?> item</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($anggaranData)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                        <h4>Tidak ada anggaran yang menunggu persetujuan</h4>
                                        <p class="text-muted">Semua anggaran telah diproses</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($anggaranData as $anggaran): ?>
                                        <div class="card approval-card pending mb-3">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h5 class="card-title">
                                                                    <?php echo htmlspecialchars($anggaran->nama_anggaran); ?>
                                                                    <span class="badge bg-warning status-badge">
                                                                        <i class="fas fa-clock"></i> Menunggu
                                                                    </span>
                                                                </h5>
                                                                <h6 class="card-subtitle mb-2 text-muted">
                                                                    <i class="fas fa-users"></i>
                                                                    <?php echo htmlspecialchars($anggaran->nama_komisi); ?>
                                                                </h6>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="amount text-primary">
                                                                    Rp <?php echo number_format($anggaran->jumlah, 0, ',', '.'); ?>
                                                                </div>
                                                                <small class="text-muted">
                                                                    Periode: <?php echo date('d/m/Y', strtotime($anggaran->tanggal_mulai)); ?> 
                                                                    - <?php echo date('d/m/Y', strtotime($anggaran->tanggal_selesai)); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                        
                                                        <p class="card-text mt-3">
                                                            <?php echo nl2br(htmlspecialchars($anggaran->deskripsi)); ?>
                                                        </p>
                                                        
                                                        <div class="timeline">
                                                            <div class="timeline-item">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-calendar-plus"></i>
                                                                    Diajukan: <?php echo date('d/m/Y H:i', strtotime($anggaran->tanggal_dibuat)); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="border-start ps-3">
                                                            <h6><i class="fas fa-tasks"></i> Aksi Persetujuan</h6>
                                                            <form method="POST" class="mt-3">
                                                                <input type="hidden" name="anggaran_id" value="<?php echo $anggaran->id; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label for="catatan_<?php echo $anggaran->id; ?>" class="form-label">
                                                                        <small>Catatan (opsional)</small>
                                                                    </label>
                                                                    <textarea class="form-control form-control-sm" 
                                                                              id="catatan_<?php echo $anggaran->id; ?>" 
                                                                              name="catatan" rows="3" 
                                                                              placeholder="Masukkan catatan jika diperlukan..."></textarea>
                                                                </div>
                                                                
                                                                <div class="action-buttons">
                                                                    <button type="submit" name="approve" 
                                                                            class="btn btn-success btn-sm flex-fill">
                                                                        <i class="fas fa-check"></i> Setujui
                                                                    </button>
                                                                    <button type="submit" name="reject" 
                                                                            class="btn btn-danger btn-sm flex-fill"
                                                                            onclick="return confirm('Yakin menolak anggaran ini?')">
                                                                        <i class="fas fa-times"></i> Tolak
                                                                    </button>
                                                                </div>
                                                            </form>
                                                            
                                                            <div class="mt-3">
                                                                <a href="detail_anggaran.php?id=<?php echo $anggaran->id; ?>" 
                                                                   class="btn btn-outline-primary btn-sm w-100">
                                                                    <i class="fas fa-eye"></i> Lihat Detail
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="card-footer">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Menunggu</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            $pendingCount = $anggaranModel->countAnggaranByStatus('pending');
                                            echo $pendingCount;
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="fas fa-clock fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Disetujui</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            $approvedCount = $anggaranModel->countAnggaranByStatus('disetujui');
                                            echo $approvedCount;
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Ditolak</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            $rejectedCount = $anggaranModel->countAnggaranByStatus('ditolak');
                                            echo $rejectedCount;
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="fas fa-times-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Diproses</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            $totalProcessed = $approvedCount + $rejectedCount;
                                            echo $totalProcessed;
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="fas fa-chart-bar fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'partials/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Auto refresh setiap 30 detik untuk update realtime
        setInterval(function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'check_new_approval.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.new_approvals > 0) {
                        // Update badge
                        document.querySelector('.badge.bg-light').textContent = data.new_approvals + ' item';
                        
                        // Show notification
                        if (Notification.permission === "granted") {
                            new Notification("Anggaran Baru", {
                                body: "Ada " + data.new_approvals + " anggaran baru menunggu persetujuan",
                                icon: "/assets/img/notification-icon.png"
                            });
                        }
                    }
                }
            };
            xhr.send();
        }, 30000); // 30 detik
        
        // Request notification permission
        if (Notification.permission === "default") {
            Notification.requestPermission();
        }
        
        // Confirmation for reject action
        document.addEventListener('DOMContentLoaded', function() {
            var rejectButtons = document.querySelectorAll('button[name="reject"]');
            rejectButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin menolak anggaran ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
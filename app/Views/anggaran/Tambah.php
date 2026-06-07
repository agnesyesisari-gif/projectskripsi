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
$allowed_roles = ['admin', 'ketua_komisi'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: index.php');
    exit();
}

$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

// Inisialisasi model
$anggaranModel = new AnggaranModel();
$komisiModel = new KomisiModel();
$programModel = new ProgramKerjaModel();

// Set tahun default
$currentYear = date('Y');

// Ambil data komisi
$komisi_list = $komisiModel->getAllKomisi();

// Jika ketua komisi, hanya bisa menambah untuk komisinya
if ($userRole == 'ketua_komisi') {
    $userKomisi = $komisiModel->getKomisiByKetua($userId);
    if (!$userKomisi) {
        header('Location: index.php');
        exit();
    }
}

// Ambil program kerja untuk referensi
$program_list = $programModel->getAllProgram();

// Inisialisasi variabel
$errors = [];
$success = false;
$anggaran_data = [
    'nama_anggaran' => '',
    'komisi_id' => $userKomisi['id'] ?? '',
    'tahun' => $currentYear,
    'deskripsi' => '',
    'nominal_diajukan' => 0,
    'tanggal_mulai' => date('Y-m-d'),
    'tanggal_selesai' => date('Y-m-d', strtotime('+30 days')),
    'status' => 'draft'
];

// Items array untuk form
$items = [
    [
        'nama_item' => '',
        'deskripsi' => '',
        'jumlah' => 1,
        'harga_satuan' => 0,
        'subtotal' => 0
    ]
];

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'save_draft' || $action == 'submit') {
        // Validasi input
        $anggaran_data['nama_anggaran'] = trim($_POST['nama_anggaran'] ?? '');
        $anggaran_data['komisi_id'] = $_POST['komisi_id'] ?? '';
        $anggaran_data['tahun'] = $_POST['tahun'] ?? $currentYear;
        $anggaran_data['deskripsi'] = trim($_POST['deskripsi'] ?? '');
        $anggaran_data['nominal_diajukan'] = str_replace(['.', ','], '', $_POST['nominal_diajukan'] ?? '0');
        $anggaran_data['tanggal_mulai'] = $_POST['tanggal_mulai'] ?? '';
        $anggaran_data['tanggal_selesai'] = $_POST['tanggal_selesai'] ?? '';
        $anggaran_data['catatan'] = trim($_POST['catatan'] ?? '');
        
        // Set status
        $anggaran_data['status'] = $action == 'submit' ? 'diajukan' : 'draft';
        $anggaran_data['created_by'] = $userId;
        
        // Validasi
        if (empty($anggaran_data['nama_anggaran'])) {
            $errors[] = 'Nama anggaran harus diisi';
        }
        
        if (empty($anggaran_data['komisi_id'])) {
            $errors[] = 'Komisi harus dipilih';
        }
        
        if (empty($anggaran_data['tanggal_mulai'])) {
            $errors[] = 'Tanggal mulai harus diisi';
        }
        
        if (empty($anggaran_data['tanggal_selesai'])) {
            $errors[] = 'Tanggal selesai harus diisi';
        }
        
        if (strtotime($anggaran_data['tanggal_selesai']) < strtotime($anggaran_data['tanggal_mulai'])) {
            $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai';
        }
        
        if ($anggaran_data['nominal_diajukan'] <= 0) {
            $errors[] = 'Nominal yang diajukan harus lebih dari 0';
        }
        
        // Process items
        $items = [];
        $item_names = $_POST['item_nama'] ?? [];
        $item_descriptions = $_POST['item_deskripsi'] ?? [];
        $item_quantities = $_POST['item_jumlah'] ?? [];
        $item_prices = $_POST['item_harga_satuan'] ?? [];
        
        $total_items = count($item_names);
        $total_amount = 0;
        
        for ($i = 0; $i < $total_items; $i++) {
            if (!empty($item_names[$i])) {
                $item_total = (float)str_replace(['.', ','], '', $item_quantities[$i] ?? '0') * 
                             (float)str_replace(['.', ','], '', $item_prices[$i] ?? '0');
                
                $items[] = [
                    'nama_item' => trim($item_names[$i]),
                    'deskripsi' => trim($item_descriptions[$i] ?? ''),
                    'jumlah' => (float)str_replace(['.', ','], '', $item_quantities[$i] ?? '0'),
                    'harga_satuan' => (float)str_replace(['.', ','], '', $item_prices[$i] ?? '0'),
                    'subtotal' => $item_total
                ];
                
                $total_amount += $item_total;
            }
        }
        
        if (empty($items)) {
            $errors[] = 'Minimal satu item harus diisi';
        }
        
        // Jika tidak ada error, simpan data
        if (empty($errors)) {
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Create anggaran
                $anggaran_id = $anggaranModel->createAnggaran($anggaran_data);
                
                if ($anggaran_id) {
                    // Save items
                    foreach ($items as $item) {
                        $item_data = [
                            'anggaran_id' => $anggaran_id,
                            'nama_item' => $item['nama_item'],
                            'deskripsi' => $item['deskripsi'],
                            'jumlah' => $item['jumlah'],
                            'harga_satuan' => $item['harga_satuan'],
                            'subtotal' => $item['subtotal'],
                            'created_by' => $userId
                        ];
                        $anggaranModel->addAnggaranItem($item_data);
                    }
                    
                    // Add log
                    $log_data = [
                        'anggaran_id' => $anggaran_id,
                        'user_id' => $userId,
                        'action' => 'create',
                        'deskripsi' => 'Anggaran dibuat',
                        'data_sebelum' => null,
                        'data_sesudah' => json_encode($anggaran_data)
                    ];
                    $anggaranModel->addAnggaranLog($log_data);
                    
                    // Commit transaction
                    $this->db->commit();
                    
                    $success = true;
                    $success_message = $action == 'submit' ? 
                        'Anggaran berhasil diajukan!' : 
                        'Anggaran berhasil disimpan sebagai draft!';
                        
                    // Redirect if submitted
                    if ($action == 'submit') {
                        header('Location: detail.php?id=' . $anggaran_id);
                        exit();
                    }
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                $errors[] = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
            }
        }
    }
    
    // Handle item operations (add/remove) via AJAX
    elseif ($action == 'add_item' || $action == 'remove_item') {
        // This would typically be handled via AJAX
        // For simplicity, we'll handle it in the main form
    }
}

// Format currency helper
function formatCurrency($amount) {
    if ($amount == 0) return '';
    return number_format($amount, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggaran - Gereja <?php echo $_SESSION['gereja_nama']; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
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
                        url('../../assets/images/budget-create.jpg') center/cover;
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
        }
        
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-section {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            background: #f8f9fa;
        }
        
        .form-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .form-section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .item-row {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .item-row:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .item-number {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .item-total {
            font-weight: bold;
            color: var(--success-color);
            font-size: 1.1rem;
        }
        
        .calculation-box {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .calculation-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .calculation-row.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--success-color);
            border-bottom: none;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .btn-draft {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
        }
        
        .btn-add-item {
            background: linear-gradient(135deg, var(--warning-color), #f1c40f);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-remove-item {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .required::after {
            content: ' *';
            color: var(--danger-color);
        }
        
        .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .budget-preview {
            background: white;
            border: 2px solid var(--secondary-color);
            border-radius: 10px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        
        .preview-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .preview-total {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--success-color);
            padding-top: 10px;
            border-top: 2px solid var(--secondary-color);
        }
        
        .currency-input {
            position: relative;
        }
        
        .currency-input::before {
            content: 'Rp';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 500;
        }
        
        .currency-input input {
            padding-left: 45px;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        
        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: var(--success-color);
            color: white;
        }
        
        .step-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .step.active .step-label {
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .item-row {
                padding: 15px;
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
                    <small class="opacity-75">Formulir Anggaran Baru</small>
                </div>
            </a>
            <div class="d-flex align-items-center">
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
                            <li class="breadcrumb-item active text-white">Tambah Baru</li>
                        </ol>
                    </nav>
                    <h1 class="h2 mb-2">Formulir Anggaran Baru</h1>
                    <p class="mb-0 opacity-75">Isi formulir berikut untuk mengajukan anggaran kegiatan</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-inline-block bg-white text-dark px-4 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Tahun <?php echo $currentYear; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if ($success && isset($success_message)): ?>
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
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Terjadi Kesalahan</h5>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Step Indicator -->
        <div class="step-indicator mb-4">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Informasi Dasar</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Item Anggaran</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Review & Submit</div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-lg-8">
                <form method="POST" action="" id="anggaranForm">
                    <!-- Section 1: Informasi Dasar -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h5 class="form-section-title">
                                <span class="form-section-number">1</span>
                                Informasi Dasar Anggaran
                            </h5>
                            <span class="badge bg-primary">Wajib diisi</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Nama Anggaran</label>
                                <input type="text" class="form-control" name="nama_anggaran" 
                                       value="<?php echo htmlspecialchars($anggaran_data['nama_anggaran']); ?>" 
                                       required placeholder="Contoh: Anggaran Retreat Pemuda 2024">
                                <div class="help-text">Berikan nama yang deskriptif dan mudah dipahami</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Komisi</label>
                                <select class="form-select" name="komisi_id" required 
                                    <?php echo isset($userKomisi) ? 'disabled' : ''; ?>>
                                    <option value="">Pilih Komisi</option>
                                    <?php foreach ($komisi_list as $komisi): ?>
                                        <option value="<?php echo $komisi['id']; ?>" 
                                            <?php echo ($anggaran_data['komisi_id'] == $komisi['id'] || 
                                                     (isset($userKomisi) && $userKomisi['id'] == $komisi['id'])) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($komisi['nama_komisi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($userKomisi)): ?>
                                    <input type="hidden" name="komisi_id" value="<?php echo $userKomisi['id']; ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Tahun</label>
                                <select class="form-select" name="tahun" required>
                                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?php echo $y; ?>" 
                                            <?php echo $anggaran_data['tahun'] == $y ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Tanggal Mulai</label>
                                <input type="date" class="form-control datepicker" name="tanggal_mulai" 
                                       value="<?php echo $anggaran_data['tanggal_mulai']; ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Tanggal Selesai</label>
                                <input type="date" class="form-control datepicker" name="tanggal_selesai" 
                                       value="<?php echo $anggaran_data['tanggal_selesai']; ?>" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label required">Nominal yang Diajukan</label>
                                <div class="currency-input">
                                    <input type="text" class="form-control" name="nominal_diajukan" 
                                           id="nominalDiajukan" 
                                           value="<?php echo formatCurrency($anggaran_data['nominal_diajukan']); ?>" 
                                           required placeholder="0">
                                </div>
                                <div class="help-text">Total dari semua item anggaran akan dihitung otomatis</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Deskripsi / Latar Belakang</label>
                                <textarea class="form-control" name="deskripsi" rows="4" 
                                          placeholder="Jelaskan latar belakang dan tujuan dari anggaran ini..."><?php echo htmlspecialchars($anggaran_data['deskripsi']); ?></textarea>
                                <div class="help-text">Deskripsi yang jelas akan membantu proses persetujuan</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Catatan Internal</label>
                                <textarea class="form-control" name="catatan" rows="3" 
                                          placeholder="Catatan untuk internal komisi..."><?php echo htmlspecialchars($anggaran_data['catatan'] ?? ''); ?></textarea>
                                <div class="help-text">Catatan ini hanya akan dilihat oleh komisi Anda</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Item Anggaran -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h5 class="form-section-title">
                                <span class="form-section-number">2</span>
                                Rincian Item Anggaran
                            </h5>
                            <button type="button" class="btn btn-add-item" onclick="addItem()">
                                <i class="fas fa-plus me-1"></i> Tambah Item
                            </button>
                        </div>
                        
                        <div id="itemsContainer">
                            <?php foreach ($items as $index => $item): ?>
                                <div class="item-row" id="itemRow<?php echo $index; ?>">
                                    <div class="item-header">
                                        <div class="item-number">Item #<?php echo $index + 1; ?></div>
                                        <div class="item-total" id="itemTotal<?php echo $index; ?>">
                                            Rp 0
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Nama Item</label>
                                            <input type="text" class="form-control item-nama" 
                                                   name="item_nama[]" 
                                                   value="<?php echo htmlspecialchars($item['nama_item']); ?>" 
                                                   placeholder="Contoh: Sewa tempat, Konsumsi, Transportasi" 
                                                   onchange="calculateTotal(<?php echo $index; ?>)">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label required">Jumlah</label>
                                            <input type="number" class="form-control item-jumlah" 
                                                   name="item_jumlah[]" 
                                                   value="<?php echo $item['jumlah']; ?>" 
                                                   min="1" step="1" 
                                                   onchange="calculateTotal(<?php echo $index; ?>)" 
                                                   onkeyup="calculateTotal(<?php echo $index; ?>)">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label required">Harga Satuan</label>
                                            <div class="currency-input">
                                                <input type="text" class="form-control item-harga" 
                                                       name="item_harga_satuan[]" 
                                                       value="<?php echo formatCurrency($item['harga_satuan']); ?>" 
                                                       placeholder="0" 
                                                       onchange="calculateTotal(<?php echo $index; ?>)" 
                                                       onkeyup="calculateTotal(<?php echo $index; ?>)">
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Deskripsi Item</label>
                                            <textarea class="form-control item-deskripsi" 
                                                      name="item_deskripsi[]" rows="2" 
                                                      placeholder="Deskripsi detail item..."><?php echo htmlspecialchars($item['deskripsi']); ?></textarea>
                                        </div>
                                        
                                        <?php if ($index > 0): ?>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-remove-item" 
                                                        onclick="removeItem(<?php echo $index; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Calculation Box -->
                        <div class="calculation-box">
                            <div class="calculation-row">
                                <span>Jumlah Item:</span>
                                <span id="totalItems"><?php echo count($items); ?></span>
                            </div>
                            <div class="calculation-row">
                                <span>Total Semua Item:</span>
                                <span id="totalAllItems">Rp 0</span>
                            </div>
                            <div class="calculation-row">
                                <span>Nominal yang Diajukan:</span>
                                <span id="totalDiajukan">Rp 0</span>
                            </div>
                            <div class="calculation-row total">
                                <span>Selisih:</span>
                                <span id="difference">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Dokumen Pendukung -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h5 class="form-section-title">
                                <span class="form-section-number">3</span>
                                Dokumen Pendukung (Opsional)
                            </h5>
                            <span class="badge bg-success">Optional</span>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Anda dapat menambahkan dokumen pendukung (proposal, RAB detail, dll) setelah anggaran dibuat
                        </div>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Link Dokumen (Google Drive, Dropbox, dll)</label>
                                <input type="url" class="form-control" 
                                       placeholder="https://drive.google.com/...">
                                <div class="help-text">Masukkan link jika dokumen sudah ada di cloud storage</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Upload Dokumen</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="dokumenFile">
                                    <button class="btn btn-outline-secondary" type="button" disabled>
                                        <i class="fas fa-upload"></i>
                                    </button>
                                </div>
                                <div class="help-text">Maksimal 10MB, format: PDF, DOC, XLS, JPG, PNG</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="submit" name="action" value="save_draft" class="btn btn-custom btn-draft">
                                <i class="fas fa-save me-1"></i> Simpan Draft
                            </button>
                            <button type="submit" name="action" value="submit" class="btn btn-custom btn-submit">
                                <i class="fas fa-paper-plane me-1"></i> Ajukan Anggaran
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Column: Preview -->
            <div class="col-lg-4">
                <div class="budget-preview">
                    <h5 class="preview-title">
                        <i class="fas fa-eye me-2"></i>
                        Preview Anggaran
                    </h5>
                    
                    <div class="preview-item">
                        <span>Status:</span>
                        <span class="badge bg-warning">Draft</span>
                    </div>
                    
                    <div class="preview-item">
                        <span>Komisi:</span>
                        <span id="previewKomisi"><?php 
                            if (isset($userKomisi)) {
                                echo htmlspecialchars($userKomisi['nama_komisi']);
                            } elseif ($anggaran_data['komisi_id']) {
                                foreach ($komisi_list as $komisi) {
                                    if ($komisi['id'] == $anggaran_data['komisi_id']) {
                                        echo htmlspecialchars($komisi['nama_komisi']);
                                        break;
                                    }
                                }
                            } else {
                                echo '-';
                            }
                        ?></span>
                    </div>
                    
                    <div class="preview-item">
                        <span>Tahun:</span>
                        <span id="previewTahun"><?php echo $anggaran_data['tahun']; ?></span>
                    </div>
                    
                    <div class="preview-item">
                        <span>Periode:</span>
                        <span id="previewPeriode">
                            <?php echo date('d M Y', strtotime($anggaran_data['tanggal_mulai'])); ?> - 
                            <?php echo date('d M Y', strtotime($anggaran_data['tanggal_selesai'])); ?>
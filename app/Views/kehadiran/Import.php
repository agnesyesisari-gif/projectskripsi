<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/KehadiranModel.php';
require_once '../../models/JadwalModel.php';

// Cek apakah user sudah login dan memiliki akses admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$allowed_roles = ['admin', 'ketua_komisi', 'sekretaris'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: dashboard.php');
    exit();
}

// Inisialisasi model
$kehadiranModel = new KehadiranModel();
$jadwalModel = new JadwalModel();

// Ambil data untuk dropdown
$bulan_list = $kehadiranModel->getBulanList();
$jenis_ibadah_list = $jadwalModel->getJenisIbadah();
$komisi_list = $jadwalModel->getAllKomisi();
$petugas_list = $kehadiranModel->getAllPetugas();

// Pesan hasil import
$import_message = '';
$import_success = false;

// Proses import manual (multiple entry)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_manual') {
    $import_data = json_decode($_POST['import_data'], true);
    $success_count = 0;
    $error_count = 0;
    
    if (is_array($import_data)) {
        foreach ($import_data as $data) {
            if (!empty($data['tanggal_ibadah']) && !empty($data['petugas_id'])) {
                $result = $kehadiranModel->addKehadiran($data);
                if ($result) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $import_message = "Import manual berhasil! $success_count data berhasil diimport.";
            $import_success = true;
            if ($error_count > 0) {
                $import_message .= " $error_count data gagal.";
            }
        } else {
            $import_message = "Import manual gagal. Tidak ada data yang berhasil diimport.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Kehadiran - Gereja <?php echo $_SESSION['gereja_nama']; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
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
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
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
        
        .upload-area {
            border: 2px dashed #ced4da;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            border-color: var(--secondary-color);
            background-color: #e7f3ff;
        }
        
        .upload-area.dragover {
            border-color: var(--success-color);
            background-color: #e8f6ef;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .preview-table {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .btn-custom {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .btn-custom:hover {
            background-color: #2980b9;
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border: none;
        }
        
        .import-row {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        
        .import-row.success {
            border-left-color: var(--success-color);
            background-color: #f0f9f0;
        }
        
        .import-row.error {
            border-left-color: var(--danger-color);
            background-color: #fdf2f2;
        }
        
        .template-btn {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            color: var(--dark);
            display: inline-block;
            transition: all 0.3s;
        }
        
        .template-btn:hover {
            background-color: #e9ecef;
            color: var(--secondary-color);
            text-decoration: none;
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
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-home"></i>
                </a>
                <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="kehadiran.php">Kehadiran</a></li>
                <li class="breadcrumb-item active">Import Data</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Import Data Kehadiran</h1>
                <p class="text-muted">Input manual</p>
            </div>
            <div>
                <a href="kehadiran.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Alert Message -->
        <?php if ($import_message): ?>
            <div class="alert alert-<?php echo $import_success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas <?php echo $import_success ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                <?php echo $import_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-label">Input Manual</div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Upload/Input Data</div>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-label">Preview & Konfirmasi</div>
            </div>
        </div>

        <!-- Step 1: Pilih Metode -->
        <div class="card card-custom" id="step1-content">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">Pilih Metode Import</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 text-center border-success">
                            <div class="card-body">
                                <div class="display-4 text-success mb-3">
                                    <i class="fas fa-keyboard"></i>
                                </div>
                                <h4 class="card-title">Input Manual</h4>
                                <p class="card-text">Input data kehadiran secara manual untuk satu atau beberapa petugas sekaligus.</p>
                                <button type="button" class="btn btn-success mt-4" onclick="showStep('manual')">
                                    <i class="fas fa-edit me-1"></i> Lanjut dengan Input Manual
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Manual Input -->
        <div class="card card-custom d-none" id="step2-manual-content">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">Input Data Kehadiran Manual</h5>
            </div>
            <div class="card-body">
                <form id="formManualInput">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Ibadah *</label>
                            <input type="date" class="form-control" id="manualTanggal" name="tanggal_ibadah" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Ibadah *</label>
                            <select class="form-select" id="manualJenisIbadah" name="jenis_ibadah_id" required>
                                <option value="">Pilih Jenis Ibadah</option>
                                <?php foreach ($jenis_ibadah_list as $jenis): ?>
                                    <option value="<?php echo $jenis['id']; ?>"><?php echo htmlspecialchars($jenis['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Komisi</label>
                            <select class="form-select" id="manualKomisi" onchange="filterPetugas()">
                                <option value="">Semua Komisi</option>
                                <?php foreach ($komisi_list as $komisi): ?>
                                    <option value="<?php echo $komisi['id']; ?>"><?php echo htmlspecialchars($komisi['nama_komisi']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Tabel Input -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="manualInputTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Petugas *</th>
                                    <th width="20%">Peran Pelayanan *</th>
                                    <th width="15%">Status *</th>
                                    <th width="15%">Jam Hadir</th>
                                    <th width="15%">Jam Pulang</th>
                                    <th width="5%">
                                        <button type="button" class="btn btn-sm btn-success" onclick="addManualRow()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="manualInputBody">
                                <!-- Baris akan ditambahkan oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Tambahkan baris untuk menginput beberapa petugas sekaligus
                    </div>
                    
                    <div class="mt-4">
                        <button type="button" class="btn btn-secondary" onclick="showStep('method')">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary float-end" onclick="previewManualData()">
                            <i class="fas fa-eye me-1"></i> Preview Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Step 3: Preview -->
        <div class="card card-custom d-none" id="step3-content">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">Preview & Konfirmasi Data</h5>
            </div>
            <div class="card-body">
                <div id="previewContent">
                    <!-- Konten preview akan diisi oleh JavaScript -->
                </div>
                
                <div class="mt-4">
                    <button type="button" class="btn btn-secondary" onclick="backToInput()">
                        <i class="fas fa-arrow-left me-1"></i> Kembali Edit
                    </button>
                    <button type="button" class="btn btn-success float-end" onclick="confirmImport()">
                        <i class="fas fa-check me-1"></i> Konfirmasi Import
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Import -->
        <?php
        $import_logs = $kehadiranModel->getImportLogs(5);
        if (!empty($import_logs)):
        ?>
        <div class="card card-custom mt-4">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">Riwayat Import Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>File</th>
                                <th>User</th>
                                <th>Berhasil</th>
                                <th>Gagal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($import_logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['filename']); ?></td>
                                    <td><?php echo htmlspecialchars($log['user_nama']); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $log['success_count']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger"><?php echo $log['error_count']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($log['success_count'] > 0): ?>
                                            <span class="badge bg-success">Berhasil</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Gagal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- XLSX Library for client-side Excel reading -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
        let currentStep = 'method';
        let importData = [];
        let importMethod = '';
        
        // Inisialisasi Select2
        $(document).ready(function() {
            $('#manualJenisIbadah').select2();
            $('#manualKomisi').select2();
            
            // Tambah baris pertama untuk manual input
            addManualRow();
        });
        
        // Fungsi untuk navigasi step
        function showStep(step) {
            // Sembunyikan semua step content
            $('#step1-content, #step2-excel-content, #step2-manual-content, #step3-content').addClass('d-none');
            
            // Update step indicator
            $('#step1, #step2, #step3').removeClass('active completed');
            
            if (step === 'method') {
                $('#step1-content').removeClass('d-none');
                $('#step1').addClass('active');
                currentStep = 'method';
            } 
            else if (step === 'excel') {
                $('#step2-excel-content').removeClass('d-none');
                $('#step1').addClass('completed');
                $('#step2').addClass('active');
                currentStep = 'excel';
                importMethod = 'excel';
            } 
            else if (step === 'manual') {
                $('#step2-manual-content').removeClass('d-none');
                $('#step1').addClass('completed');
                $('#step2').addClass('active');
                currentStep = 'manual';
                importMethod = 'manual';
            }
        }
        
        // Fungsi untuk menangani drag & drop
        const dropArea = document.getElementById('dropArea');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('dragover');
        }
        
        function unhighlight() {
            dropArea.classList.remove('dragover');
        }
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }
        
        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }
        
        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                const validExtensions = ['.xls', '.xlsx'];
                const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
                
                if (validExtensions.includes(fileExtension)) {
                    // Tampilkan nama file
                    $('#fileName').text(file.name + ' (' + formatBytes(file.size) + ')');
                    $('#selectedFile').removeClass('d-none');
                    
                    // Baca file Excel untuk preview
                    readExcelFile(file);
                } else {
                    alert('Format file tidak didukung. Harap pilih file Excel (.xls atau .xlsx)');
                }
            }
        }
        
        function clearFile() {
            $('#fileInput').val('');
            $('#selectedFile').addClass('d-none');
            $('#filePreview').addClass('d-none');
        }
        
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
        
        function readExcelFile(file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
                
                // Tampilkan preview
                displayPreview(jsonData.slice(0, 10)); // Tampilkan 10 baris pertama
                $('#filePreview').removeClass('d-none');
                
                // Simpan data untuk preview
                importData = jsonData.slice(1).map(row => {
                    return {
                        tanggal_ibadah: row[0] || '',
                        jenis_ibadah: row[1] || '',
                        nama_petugas: row[2] || '',
                        peran_pelayanan: row[3] || '',
                        status_kehadiran: row[4] || 'hadir',
                        jam_hadir: row[5] || '',
                        jam_pulang: row[6] || '',
                        alasan_izin: row[7] || '',
                        keterangan: row[8] || ''
                    };
                }).filter(item => item.tanggal_ibadah && item.nama_petugas);
            };
            
            reader.readAsArrayBuffer(file);
        }
        
        function displayPreview(data) {
            const tbody = $('#previewTableBody');
            tbody.empty();
            
            // Skip header row (index 0)
            for (let i = 1; i < Math.min(data.length, 6); i++) {
                const row = data[i];
                const tr = $('<tr>');
                
                // Tampilkan kolom A-E
                for (let j = 0; j < 5; j++) {
                    const td = $('<td>');
                    td.text(row[j] || '');
                    tr.append(td);
                }
                
                tbody.append(tr);
            }
        }
        
        // Fungsi untuk manual input
        function addManualRow(petugasId = '', peran = '', status = 'hadir', jamHadir = '', jamPulang = '') {
            const tbody = $('#manualInputBody');
            const rowCount = tbody.find('tr').length + 1;
            
            const tr = $(`
                <tr>
                    <td>${rowCount}</td>
                    <td>
                        <select class="form-select form-select-sm petugas-select" name="petugas_id[]" required>
                            <option value="">Pilih Petugas</option>
                            ${generatePetugasOptions(petugasId)}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="peran_pelayanan[]" 
                               value="${peran}" required>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" name="status_kehadiran[]" required>
                            <option value="hadir" ${status === 'hadir' ? 'selected' : ''}>Hadir</option>
                            <option value="izin" ${status === 'izin' ? 'selected' : ''}>Izin</option>
                            <option value="alfa" ${status === 'alfa' ? 'selected' : ''}>Alfa</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" class="form-control form-control-sm" name="jam_hadir[]" value="${jamHadir}">
                    </td>
                    <td>
                        <input type="time" class="form-control form-control-sm" name="jam_pulang[]" value="${jamPulang}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeManualRow(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
            
            tbody.append(tr);
            $(tr).find('.petugas-select').select2();
        }
        
        function generatePetugasOptions(selectedId = '') {
            // Ini adalah contoh, dalam implementasi asli akan diisi dari PHP
            return `<?php 
                foreach ($petugas_list as $petugas): 
                    $selected = $petugas['id'] == selectedId ? 'selected' : '';
                    echo '<option value="' . $petugas['id'] . '" ' . $selected . '>' 
                         . htmlspecialchars($petugas['nama']) . ' (' . htmlspecialchars($petugas['nama_komisi']) . ')</option>';
                endforeach; 
            ?>`;
        }
        
        function removeManualRow(button) {
            $(button).closest('tr').remove();
            // Update nomor urut
            $('#manualInputBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }
        
        function filterPetugas() {
            const komisiId = $('#manualKom
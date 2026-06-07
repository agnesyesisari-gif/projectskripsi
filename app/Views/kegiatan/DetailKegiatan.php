<?= $this->extend('template/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white py-2 px-3 rounded shadow-sm">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('kegiatan') ?>"><i class="fas fa-list"></i> Kegiatan Pelayanan</a></li>
                    <li class="breadcrumb-item active"><i class="fas fa-info-circle"></i> Detail Kegiatan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Detail Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-hands-helping mr-2"></i><?= $kegiatan['nama_kegiatan'] ?></h4>
                            <small class="opacity-75">
                                <i class="fas fa-tag mr-1"></i>Kode: <?= $kegiatan['kode_kegiatan'] ?>
                                <?php if($kegiatan['program_kerja_id']): ?>
                                    | <i class="fas fa-project-diagram mr-1"></i>Bagian dari Program Kerja
                                <?php endif; ?>
                            </small>
                        </div>
                        <div>
                            <?php
                            $status_badge = [
                                'draft' => 'secondary',
                                'terencana' => 'info',
                                'berlangsung' => 'warning',
                                'selesai' => 'success',
                                'batal' => 'danger'
                            ];
                            ?>
                            <span class="badge badge-<?= $status_badge[$kegiatan['status']] ?> badge-lg p-2">
                                <i class="fas fa-circle mr-1"></i><?= ucfirst($kegiatan['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 border rounded">
                                <div class="stat-icon text-primary mb-2">
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                                <h5 class="mb-1"><?= date('d M Y', strtotime($kegiatan['tanggal_mulai'])) ?></h5>
                                <small class="text-muted">Tanggal Mulai</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 border rounded">
                                <div class="stat-icon text-success mb-2">
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                                <h5 class="mb-1"><?= date('d M Y', strtotime($kegiatan['tanggal_selesai'])) ?></h5>
                                <small class="text-muted">Tanggal Selesai</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 border rounded">
                                <div class="stat-icon text-info mb-2">
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                                <h5 class="mb-1">Rp <?= number_format($kegiatan['anggaran'], 0, ',', '.') ?></h5>
                                <small class="text-muted">Anggaran</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="detailTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                                <i class="fas fa-info-circle mr-1"></i>Informasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline" role="tab">
                                <i class="fas fa-stream mr-1"></i>Timeline
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="peserta-tab" data-toggle="tab" href="#peserta" role="tab">
                                <i class="fas fa-user-friends mr-1"></i>Peserta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#dokumen" role="tab">
                                <i class="fas fa-file-alt mr-1"></i>Dokumen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="anggaran-tab" data-toggle="tab" href="#anggaran" role="tab">
                                <i class="fas fa-calculator mr-1"></i>Anggaran
                            </a>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="detailTabContent">
                        <!-- Tab 1: Informasi -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="35%" class="font-weight-bold text-primary">Jenis Kegiatan:</td>
                                            <td>
                                                <?php
                                                $jenis_badge = [
                                                    'ibadah' => 'primary',
                                                    'sosial' => 'warning',
                                                    'lainnya' => 'secondary'
                                                ];
                                                ?>
                                                <span class="badge badge-<?= $jenis_badge[$kegiatan['jenis_kegiatan']] ?>">
                                                    <?= ucfirst($kegiatan['jenis_kegiatan']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-primary">Lokasi:</td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                                <?= $kegiatan['lokasi'] ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-primary">Penanggung Jawab:</td>
                                            <td>
                                                <i class="fas fa-user-check text-success mr-1"></i>
                                                <?= $kegiatan['penanggung_jawab'] ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-align-left mr-2"></i>Deskripsi Kegiatan</h5>
                                    <div class="bg-light p-3 rounded">
                                        <?= nl2br($kegiatan['deskripsi']) ?>
                                    </div>
                                </div>
                            </div>

                            <?php endif; ?>
                        </div>

                        <!-- Tab 2: Timeline -->
                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                            <?php if(!empty($timeline)): ?>
                                <div class="timeline">
                                    <?php foreach($timeline as $index => $item): ?>
                                    <div class="timeline-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
                                        <div class="timeline-date">
                                            <?= date('d M Y', strtotime($item['tanggal'])) ?>
                                            <br>
                                            <small><?= date('H:i', strtotime($item['waktu'])) ?></small>
                                        </div>
                                        <div class="timeline-content card">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= $item['kegiatan'] ?></h6>
                                                <p class="card-text"><?= nl2br($item['deskripsi']) ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-user mr-1"></i><?= $item['penanggung_jawab'] ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-stream fa-4x text-muted mb-3"></i>
                                    <h5>Belum ada timeline</h5>
                                    <p class="text-muted">Timeline kegiatan belum dibuat</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab 3: Peserta -->
                        <div class="tab-pane fade" id="peserta" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-0">Total Peserta</h6>
                                                    <h3 class="mb-0 text-primary"><?= $total_peserta ?></h3>
                                                    <small>Terdaftar</small>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-users fa-3x text-primary opacity-50"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-0">Kehadiran</h6>
                                                    <h3 class="mb-0 text-success"><?= $peserta_hadir ?></h3>
                                                    <small>Peserta hadir</small>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-user-check fa-3x text-success opacity-50"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover" id="tablePeserta">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>Umur</th>
                                            <th>No. HP</th>
                                            <th>Status</th>
                                            <th>Kehadiran</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($peserta)): ?>
                                            <?php foreach($peserta as $index => $p): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= $p['nama'] ?></strong><br>
                                                    <small class="text-muted"><?= $p['email'] ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info"><?= $p['jabatan'] ?></span><br>
                                                    <small><?= $p['departemen'] ?></small>
                                                </td>
                                                <td><?= $p['umur'] ?> tahun</td>
                                                <td><?= $p['no_hp'] ?></td>
                                                <td>
                                                    <?php
                                                    $status_peserta = [
                                                        'terdaftar' => 'info',
                                                        'konfirmasi' => 'primary',
                                                        'batal' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge badge-<?= $status_peserta[$p['status']] ?>">
                                                        <?= ucfirst($p['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($p['kehadiran'] == 'hadir'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Hadir
                                                        </span>
                                                    <?php elseif($p['kehadiran'] == 'izin'): ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-user-clock"></i> Izin
                                                        </span>
                                                    <?php elseif($p['kehadiran'] == 'sakit'): ?>
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-procedures"></i> Sakit
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-times"></i> Tidak Hadir
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info btn-detail-peserta" 
                                                            data-id="<?= $p['id'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                                    <h5>Belum ada peserta</h5>
                                                    <p class="text-muted">Belum ada peserta yang terdaftar</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 4: Dokumen -->
                        <div class="tab-pane fade" id="dokumen" role="tabpanel">
                            <div class="row">
                                <?php if(!empty($dokumen)): ?>
                                    <?php foreach($dokumen as $doc): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card document-card h-100">
                                            <div class="card-body text-center">
                                                <div class="document-icon mb-3">
                                                    <?php
                                                    $ext = pathinfo($doc['nama_file'], PATHINFO_EXTENSION);
                                                    $icon = 'fa-file';
                                                    $color = 'primary';
                                                    
                                                    if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                        $icon = 'fa-image';
                                                        $color = 'success';
                                                    } elseif(in_array($ext, ['pdf'])) {
                                                        $icon = 'fa-file-pdf';
                                                        $color = 'danger';
                                                    } elseif(in_array($ext, ['doc', 'docx'])) {
                                                        $icon = 'fa-file-word';
                                                        $color = 'info';
                                                    } elseif(in_array($ext, ['xls', 'xlsx'])) {
                                                        $icon = 'fa-file-excel';
                                                        $color = 'success';
                                                    }
                                                    ?>
                                                    <i class="fas <?= $icon ?> fa-4x text-<?= $color ?>"></i>
                                                </div>
                                                <h6 class="document-title"><?= $doc['judul'] ?></h6>
                                                <small class="text-muted"><?= $doc['tipe_dokumen'] ?></small>
                                                <div class="mt-3">
                                                    <a href="<?= base_url('uploads/dokumen/' . $doc['nama_file']) ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-footer text-center bg-transparent">
                                                <small class="text-muted">
                                                    Upload: <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                            <h5>Belum ada dokumen</h5>
                                            <p class="text-muted">Belum ada dokumen yang diupload</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tab 5: Anggaran -->
                        <div class="tab-pane fade" id="anggaran" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Anggaran Direncanakan</h6>
                                            <h3 class="text-primary">Rp <?= number_format($kegiatan['anggaran'], 0, ',', '.') ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Total Realisasi</h6>
                                            <h3 class="text-success">Rp <?= number_format($total_realisasi, 0, ',', '.') ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Sisa Anggaran</h6>
                                            <h3 class="<?= $sisa_anggaran >= 0 ? 'text-info' : 'text-danger' ?>">
                                                Rp <?= number_format($sisa_anggaran, 0, ',', '.') ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th>Jenis</th>
                                            <th>Jumlah</th>
                                            <th>Dokumen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($anggaran_detail)): ?>
                                            <?php foreach($anggaran_detail as $index => $anggaran): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= date('d/m/Y', strtotime($anggaran['tanggal'])) ?></td>
                                                <td><?= $anggaran['keterangan'] ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $anggaran['jenis'] == 'pemasukan' ? 'success' : 'danger' ?>">
                                                        <?= ucfirst($anggaran['jenis']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    Rp <?= number_format($anggaran['jumlah'], 0, ',', '.') ?>
                                                </td>
                                                <td>
                                                    <?php if($anggaran['bukti']): ?>
                                                        <a href="<?= base_url('uploads/bukti/' . $anggaran['bukti']) ?>" 
                                                           class="btn btn-sm btn-outline-info" target="_blank">
                                                            <i class="fas fa-eye"></i> Lihat
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                                    <h5>Belum ada transaksi</h5>
                                                    <p class="text-muted">Belum ada pencatatan anggaran</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-right">Total Realisasi:</th>
                                            <th class="text-right text-success">
                                                Rp <?= number_format($total_realisasi, 0, ',', '.') ?>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus mr-1"></i>
                                Dibuat: <?= date('d M Y H:i', strtotime($kegiatan['created_at'])) ?>
                                <?php if($kegiatan['updated_at'] != $kegiatan['created_at']): ?>
                                    | <i class="fas fa-edit ml-2 mr-1"></i>
                                    Diupdate: <?= date('d M Y H:i', strtotime($kegiatan['updated_at'])) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div>
                            <span class="badge badge-light">
                                ID: <?= $kegiatan['id'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Kerja Related -->
            <?php if($program_kerja): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-project-diagram mr-2"></i>Program Kerja Terkait</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="program-icon mb-2">
                                    <i class="fas fa-tasks fa-3x text-info"></i>
                                </div>
                                <h6><?= $program_kerja['nama_program'] ?></h6>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <table class="table table-sm">
                                <tr>
                                    <td width="25%" class="font-weight-bold">Tahun:</td>
                                    <td><?= $program_kerja['tahun'] ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Komisi:</td>
                                    <td><?= $program_kerja['komisi'] ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Penanggung Jawab:</td>
                                    <td><?= $program_kerja['penanggung_jawab'] ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status:</td>
                                    <td>
                                        <?php
                                        $status_program = [
                                            'draft' => 'secondary',
                                            'disetujui' => 'info',
                                            'berjalan' => 'warning',
                                            'selesai' => 'success'
                                        ];
                                        ?>
                                        <span class="badge badge-<?= $status_program[$program_kerja['status']] ?>">
                                            <?= ucfirst($program_kerja['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <a href="<?= base_url('programkerja/detail/' . $program_kerja['id']) ?>" 
                               class="btn btn-sm btn-outline-info">
                                <i class="fas fa-external-link-alt mr-1"></i> Lihat Detail Program
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Buttons -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs mr-2"></i>Aksi</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= base_url('kegiatan/edit/' . $kegiatan['id']) ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit text-warning mr-2"></i>Edit Kegiatan
                        </a>
                        
                        <?php if($kegiatan['status'] == 'selesai'): ?>
                        <a href="<?= base_url('laporan/kegiatan/' . $kegiatan['id']) ?>" class="list-group-item list-group-item-action" target="_blank">
                            <i class="fas fa-file-pdf text-danger mr-2"></i>Cetak Laporan
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('kegiatan/duplikat/' . $kegiatan['id']) ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-copy text-info mr-2"></i>Duplikat Kegiatan
                        </a>
                        
                        <button class="list-group-item list-group-item-action" onclick="shareKegiatan()">
                            <i class="fas fa-share-alt text-primary mr-2"></i>Bagikan
                        </button>
                        
                        <a href="<?= base_url('hapus/kegiatan/' . $kegiatan['id']) ?>" 
                           class="list-group-item list-group-item-action text-danger"
                           onclick="return confirm('Yakin ingin menghapus kegiatan ini?')">
                            <i class="fas fa-trash mr-2"></i>Hapus Kegiatan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Jadwal Ibadah Terkait -->
            <?php if(!empty($jadwal_terkait)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-purple text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i>Jadwal Ibadah Terkait</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach($jadwal_terkait as $jadwal): ?>
                        <a href="<?= base_url('jadwal/detail/' . $jadwal['id']) ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= $jadwal['nama_ibadah'] ?></h6>
                                <small><?= date('H:i', strtotime($jadwal['waktu_mulai'])) ?></small>
                            </div>
                            <p class="mb-1"><?= $jadwal['tempat'] ?></p>
                            <small><i class="fas fa-user mr-1"></i><?= $jadwal['pemimpin_ibadah'] ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Statistik Singkat</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Progress Kegiatan</small>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-<?= $progress_color ?>" 
                                 role="progressbar" 
                                 style="width: <?= $progress ?>%"
                                 aria-valuenow="<?= $progress ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <small class="float-right"><?= $progress ?>%</small>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h5 class="mb-0 text-info"><?= $total_dokumen ?></h5>
                                <small>Dokumen</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h5 class="mb-0 text-success"><?= $total_timeline ?></h5>
                                <small>Timeline</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Peserta -->
<div class="modal fade" id="modalDetailPeserta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Peserta</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailPesertaContent">
                <!-- Content akan diisi via AJAX -->
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        opacity: 0.8;
    }
    
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
        transform: translateX(-50%);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        width: 50%;
        padding: 0 40px;
    }
    
    .timeline-item.left {
        left: 0;
    }
    
    .timeline-item.right {
        left: 50%;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        top: 15px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #007bff;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #007bff;
    }
    
    .timeline-item.left::before {
        right: -10px;
    }
    
    .timeline-item.right::before {
        left: -10px;
    }
    
    .timeline-content {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .timeline-date {
        position: absolute;
        top: 15px;
        padding: 5px 10px;
        background: #007bff;
        color: white;
        border-radius: 4px;
        font-weight: bold;
    }
    
    .timeline-item.left .timeline-date {
        right: -120px;
    }
    
    .timeline-item.right .timeline-date {
        left: -120px;
    }
    
    .document-card {
        transition: transform 0.2s;
        border: 1px solid #e9ecef;
    }
    
    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15
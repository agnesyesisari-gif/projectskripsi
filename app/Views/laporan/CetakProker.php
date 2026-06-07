<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Program Kerja</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PROGRAM KERJA</h2>
        <p>GEREJA <?= strtoupper($nama_gereja) ?></p>
        <p>Tahun: <?= $tahun ?><?= $status ? ' - Status: ' . ucfirst($status) : '' ?></p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Program</th>
                <th width="15%">Bidang</th>
                <th width="15%">Tanggal Mulai</th>
                <th width="15%">Tanggal Selesai</th>
                <th width="15%">Anggaran</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($laporan)): ?>
                <?php $no = 1; ?>
                <?php foreach($laporan as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= $row->nama_program ?></td>
                        <td><?= $row->bidang ?></td>
                        <td><?= date('d-m-Y', strtotime($row->tanggal_mulai)) ?></td>
                        <td><?= date('d-m-Y', strtotime($row->tanggal_selesai)) ?></td>
                        <td>Rp <?= number_format($row->anggaran, 0, ',', '.') ?></td>
                        <td>
                            <?php 
                            $status_class = '';
                            switch($row->status) {
                                case 'rencana': $status_class = 'badge-primary'; break;
                                case 'berjalan': $status_class = 'badge-warning'; break;
                                case 'selesai': $status_class = 'badge-success'; break;
                                case 'ditunda': $status_class = 'badge-danger'; break;
                                default: $status_class = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $status_class ?>">
                                <?= ucfirst($row->status) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data program kerja</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <div style="margin-bottom: 60px;">
            <?= date('d F Y') ?>
        </div>
        <div>
            <strong>Ketua Bidang Program</strong>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Cetak Laporan</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
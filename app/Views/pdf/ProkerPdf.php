<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #2c3e50;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #34495e;
        }
        .info {
            margin-bottom: 15px;
            text-align: center;
        }
        .info p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-rencana { background-color: #fff3cd; }
        .status-berjalan { background-color: #d1ecf1; }
        .status-selesai { background-color: #d4edda; }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #7f8c8d;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin-top: 60px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo $gereja_name; ?></h1>
        <h2><?php echo $title; ?></h2>
    </div>
    
    <div class="info">
        <p><strong><?php echo $status_text; ?></strong></p>
        <p>Dibuat pada: <?php echo $generated_date; ?></p>
    </div>
    
    <?php if (!empty($programs)): ?>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Program</th>
                <th width="15%">Tanggal Mulai</th>
                <th width="15%">Tanggal Selesai</th>
                <th width="15%">Tempat</th>
                <th width="15%">Penanggung Jawab</th>
                <th width="10%">Status</th>
                <th width="5%">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($programs as $item): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $item->nama_program; ?></td>
                <td><?php echo date('d M Y', strtotime($item->tanggal_mulai)); ?></td>
                <td><?php echo $item->tanggal_selesai ? date('d M Y', strtotime($item->tanggal_selesai)) : '-'; ?></td>
                <td><?php echo $item->tempat ?: '-'; ?></td>
                <td><?php echo $item->penanggung_jawab ?: '-'; ?></td>
                <td class="status-<?php echo $item->status; ?>">
                    <?php echo ucfirst($item->status); ?>
                </td>
                <td><?php echo $item->deskripsi ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">
        <p>Tidak ada data program kerja untuk ditampilkan.</p>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi Gereja</p>
    </div>
    
    <div class="signature">
        <div class="signature-line"></div>
        <p>Ketua Bidang Pelayanan</p>
    </div>
</body>
</html>
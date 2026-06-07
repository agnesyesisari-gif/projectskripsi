<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        .info { margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .summary { margin-top: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Rekap Kehadiran Jemaat</h2>
    <div class="info">
        <p><strong>Kegiatan:</strong> <?= esc($kegiatan['nama_kegiatan'] ?? '-') ?></p>
        <p><strong>Tanggal:</strong> <?= isset($kegiatan['tanggal']) ? date('d M Y', strtotime($kegiatan['tanggal'])) : '-' ?></p>
        <p><strong>Dicetak:</strong> <?= $export_date ?? date('d/m/Y H:i:s') ?></p>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Nama Jemaat</th><th>Waktu Hadir</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($kehadiran)): ?>
                <?php foreach ($kehadiran as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($item['nama'] ?? $item['nama_jemaat'] ?? '-') ?></td>
                        <td><?= esc($item['waktu_hadir'] ?? '-') ?></td>
                        <td><?= esc($item['keterangan'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="summary">
        <p>Total Hadir: <?= $total_hadir ?? 0 ?> dari <?= $total_jemaat ?? 0 ?> jemaat (<?= $persentase ?? 0 ?>%)</p>
    </div>
</body>
</html>

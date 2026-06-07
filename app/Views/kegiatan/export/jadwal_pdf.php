<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Ibadah</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Jadwal Ibadah</h2>
    <p style="text-align:center">Dicetak: <?= date('d M Y H:i') ?></p>
    <table>
        <thead>
            <tr><th>#</th><th>Nama Kegiatan</th><th>Tanggal</th><th>Waktu</th><th>Tempat</th><th>Penanggung Jawab</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($kegiatan)): ?>
                <?php foreach ($kegiatan as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($item['nama_kegiatan'] ?? '-') ?></td>
                        <td><?= date('d M Y', strtotime($item['tanggal'] ?? 'now')) ?></td>
                        <td><?= esc($item['waktu_mulai'] ?? '-') ?></td>
                        <td><?= esc($item['tempat'] ?? $item['lokasi'] ?? '-') ?></td>
                        <td><?= esc($item['penanggung_jawab'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

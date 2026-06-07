<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Anggaran</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .total-row { font-weight: bold; background: #d4edda; }
    </style>
</head>
<body>
    <h2>Laporan Anggaran Tahun <?= $tahun ?? date('Y') ?></h2>
    <p style="text-align:center">Periode: <?= ucfirst($periode ?? 'tahunan') ?> | Dicetak: <?= $tanggal_cetak ?? date('d-m-Y H:i:s') ?></p>
    <table>
        <thead>
            <tr><th>#</th><th>Nama Anggaran</th><th>Program</th><th>Jumlah</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan)): ?>
                <?php foreach ($laporan as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($item['nama_anggaran'] ?? '-') ?></td>
                        <td><?= esc($item['nama_program'] ?? '-') ?></td>
                        <td>Rp <?= number_format($item['jumlah'] ?? 0, 0, ',', '.') ?></td>
                        <td><?= ucfirst($item['status'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right">Total</td>
                    <td>Rp <?= number_format($total_anggaran ?? 0, 0, ',', '.') ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

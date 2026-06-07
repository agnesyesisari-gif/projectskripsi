<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 14px;
            color: #2c3e50;
        }
        .header h2 {
            margin: 3px 0;
            font-size: 12px;
            color: #34495e;
        }
        .calendar-month {
            text-align: center;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .calendar-table th {
            background-color: #34495e;
            color: white;
            padding: 5px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .calendar-table td {
            width: 14.28%;
            height: 80px;
            vertical-align: top;
            padding: 3px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        .calendar-day {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .event {
            margin: 1px 0;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 7px;
            line-height: 1.1;
        }
        .event-ibadah {
            background-color: #3498db;
            color: white;
        }
        .event-program {
            background-color: #e74c3c;
            color: white;
        }
        .other-month {
            background-color: #f8f9fa;
            color: #ccc;
        }
        .info {
            margin-bottom: 10px;
            text-align: center;
        }
        .legend {
            margin: 10px 0;
            text-align: center;
        }
        .legend-item {
            display: inline-block;
            margin: 0 10px;
            font-size: 8px;
        }
        .legend-color {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 3px;
            vertical-align: middle;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo $gereja_name; ?></h1>
        <h2><?php echo $title; ?></h2>
    </div>
    
    <div class="info">
        <p><strong>Periode: <?php echo $month_name . ' ' . $year; ?></strong></p>
        <p>Dibuat pada: <?php echo $generated_date; ?></p>
    </div>
    
    <div class="calendar-month">
        <?php echo $month_name . ' ' . $year; ?>
    </div>
    
    <table class="calendar-table">
        <thead>
            <tr>
                <th>Minggu</th>
                <th>Senin</th>
                <th>Selasa</th>
                <th>Rabu</th>
                <th>Kamis</th>
                <th>Jumat</th>
                <th>Sabtu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($calendar_data as $week): ?>
            <tr>
                <?php foreach ($week as $day): ?>
                <td <?php echo empty($day['day']) ? 'class="other-month"' : ''; ?>>
                    <?php if (!empty($day['day'])): ?>
                        <div class="calendar-day"><?php echo $day['day']; ?></div>
                        <?php if (!empty($day['events'])): ?>
                            <?php foreach ($day['events'] as $event): ?>
                                <div class="event event-<?php echo $event['type']; ?>">
                                    <?php echo $event['title']; ?>
                                    <?php if ($event['type'] == 'ibadah'): ?>
                                        <br><?php echo date('H:i', strtotime($event['time'])); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="legend">
        <div class="legend-item">
            <span class="legend-color" style="background-color: #3498db;"></span>
            Jadwal Ibadah
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background-color: #e74c3c;"></span>
            Program Kerja
        </div>
    </div>
    
    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi Gereja</p>
    </div>
</body>
</html>
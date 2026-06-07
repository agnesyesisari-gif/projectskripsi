<?php echo view('Layout/Header', ['title' => $title ?? 'Kalender Kegiatan']); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<div class="container mt-4">
    <h4 class="mb-4"><i class="fas fa-calendar text-primary me-2"></i>Kalender Kegiatan</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = <?= json_encode($events ?? []) ?>;
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        events: events
    });
    calendar.render();
});
</script>
<?php echo view('Layout/Footer'); ?>

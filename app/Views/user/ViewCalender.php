<div class="main-content">
    <div class="container">
        <div class="section-title">
            <h2>Kalender Kegiatan</h2>
            <p>Jadwal ibadah dan kegiatan pelayanan gereja</p>
        </div>
        
        <!-- Calendar Container -->
        <div id="calendar"></div>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number" id="totalEvents">0</span>
                <span class="stat-label">Total Kegiatan</span>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="upcomingEvents">0</span>
                <span class="stat-label">Kegiatan Mendatang</span>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="ibadahCount">0</span>
                <span class="stat-label">Ibadah</span>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="pelayananCount">0</span>
                <span class="stat-label">Pelayanan</span>
            </div>
        </div>
    </div>
</div>

<!-- Load CSS -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/calendar.css'); ?>">

<!-- Load JavaScript -->
<script src="<?php echo base_url('assets/js/calendar.js'); ?>"></script>

<script>
// Pass PHP events to JavaScript
const phpEvents = <?php echo json_encode($events); ?>;
</script>
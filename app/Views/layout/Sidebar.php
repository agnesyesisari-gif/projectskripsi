<!-- views/templates/sidebar_admin.php -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="text-white">Admin Panel</h4>
        <small><?= session()->get('nama') ?></small>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-header">Main Navigation</li>
        
        <li class="<?= (current_url() == base_url('admin/dashboard')) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/dashboard') ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-header">Manajemen Ibadah</li>
        
        <li class="<?= (strpos(current_url(), 'admin/jadwal-ibadah') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/jadwal-ibadah') ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal Ibadah</span>
            </a>
        </li>
        
        <li class="<?= (strpos(current_url(), 'admin/jenis-ibadah') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/jenis-ibadah') ?>">
                <i class="fas fa-church"></i>
                <span>Jenis Ibadah</span>
            </a>
        </li>
        
        <li class="menu-header">Program Kerja</li>
        
        <li class="<?= (strpos(current_url(), 'admin/program-kerja') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/program-kerja') ?>">
                <i class="fas fa-tasks"></i>
                <span>Program Kerja</span>
            </a>
        </li>
        
        <li class="<?= (strpos(current_url(), 'admin/kategori-program') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/kategori-program') ?>">
                <i class="fas fa-folder"></i>
                <span>Kategori Program</span>
            </a>
        </li>
        
        <li class="menu-header">Laporan</li>
        
        <li class="<?= (strpos(current_url(), 'admin/laporan') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/laporan') ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Laporan Kegiatan</span>
            </a>
        </li>
        
        <li class="menu-header">Pengaturan</li>
        
        <li class="<?= (strpos(current_url(), 'admin/pengguna') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/pengguna') ?>">
                <i class="fas fa-users"></i>
                <span>Manajemen Pengguna</span>
            </a>
        </li>
        
        <li class="<?= (strpos(current_url(), 'admin/pengaturan') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('admin/pengaturan') ?>">
                <i class="fas fa-cog"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    background: #2c3e50;
    position: fixed;
    left: 0;
    top: 0;
    transition: all 0.3s;
    z-index: 999;
}

.sidebar-header {
    padding: 20px;
    background: #34495e;
    border-bottom: 1px solid #46627f;
}

.sidebar-header h4 {
    margin: 0;
    font-size: 18px;
}

.sidebar-header small {
    color: #bdc3c7;
    font-size: 12px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-header {
    padding: 10px 20px;
    color: #95a5a6;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.sidebar-menu li a:hover {
    background: #34495e;
    color: #fff;
    border-left-color: #3498db;
}

.sidebar-menu li.active a {
    background: #34495e;
    color: #fff;
    border-left-color: #3498db;
}

.sidebar-menu li a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}
</style>
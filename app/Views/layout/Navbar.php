<!-- views/templates/navbar_user.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url() ?>">
            <i class="fas fa-church"></i>
            Gereja <?= getSetting('nama_gereja') ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item <?= (current_url() == base_url()) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= base_url() ?>">
                        <i class="fas fa-home"></i> Beranda
                    </a>
                </li>
                
                <li class="nav-item <?= (strpos(current_url(), 'jadwal-ibadah') !== false) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= base_url('jadwal-ibadah') ?>">
                        <i class="fas fa-calendar-alt"></i> Jadwal Ibadah
                    </a>
                </li>
                
                <li class="nav-item <?= (strpos(current_url(), 'program-kerja') !== false) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= base_url('program-kerja') ?>">
                        <i class="fas fa-tasks"></i> Program Kerja
                    </a>
                </li>
                
                <li class="nav-item <?= (strpos(current_url(), 'galeri') !== false) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= base_url('galeri') ?>">
                        <i class="fas fa-images"></i> Galeri
                    </a>
                </li>
                
                <li class="nav-item <?= (strpos(current_url(), 'tentang') !== false) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= base_url('tentang') ?>">
                        <i class="fas fa-info-circle"></i> Tentang
                    </a>
                </li>
                
                <li class="nav-item <?= (strpos(current_url(), 'kontak') !== false) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= base_url('kontak') ?>">
                        <i class="fas fa-envelope"></i> Kontak
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if(session()->get('isLoggedIn')): ?>
                    <?php if(session()->get('role') == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-sm text-white me-2" href="<?= base_url('admin/dashboard') ?>">
                                <i class="fas fa-cog"></i> Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= session()->get('nama') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('profil') ?>">
                                <i class="fas fa-user-edit"></i> Profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= base_url('logout') ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('login') ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary btn-sm text-white ms-2" href="<?= base_url('register') ?>">
                            Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
.navbar-brand {
    font-weight: bold;
}

.navbar-brand i {
    margin-right: 8px;
}

.navbar-nav .nav-item .nav-link {
    padding: 8px 15px;
    transition: all 0.3s;
}

.navbar-nav .nav-item.active .nav-link {
    color: #fff;
    background: rgba(255,255,255,0.1);
    border-radius: 5px;
}

.navbar-nav .nav-link i {
    margin-right: 5px;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.dropdown-item {
    padding: 8px 15px;
}

.dropdown-item i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}
</style>
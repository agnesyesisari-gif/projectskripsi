<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? esc($pageTitle).' - ' : '' ?>GKJ PENARUBAN</title>

    <!-- Bootstrap 3 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Font Awesome 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        /* ============================================
           RESET & BASE
        ============================================ */
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ecf0f1;
            overflow-x: hidden;
        }

        /* ============================================
           SIDEBAR
        ============================================ */
        .main-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 220px;
            min-height: 100vh;
            background: #222d32;
            z-index: 810;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width .3s;
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            background: #1a2226;
            padding: 13px 14px;
            min-height: 60px;
            border-bottom: 1px solid #111;
        }
        .brand-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #3c8dbc;
            color: #fff;
            font-size: 16px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-right: 10px;
        }
        .brand-text {
            color: #fff; font-size: 13px;
            font-weight: 700; line-height: 1.3;
        }

        /* User panel */
        .sidebar-user {
            display: flex; align-items: center;
            background: #1a2226;
            padding: 12px 14px;
            border-bottom: 1px solid #111;
        }
        .user-img {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #607d8b;
            color: #fff; font-size: 16px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-right: 10px;
        }
        .user-name  { color: #fff; font-size: 13px; font-weight: 600; display: block; }
        .user-role  { color: #8aa4af; font-size: 11px; display: block; }

        /* Menu */
        .sidebar-menu { list-style: none; padding: 6px 0; margin: 0; }
        .sidebar-menu > li > a {
            display: flex; align-items: center;
            padding: 11px 15px;
            color: #b8c7ce;
            font-size: 13.5px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all .2s;
        }
        .sidebar-menu > li > a:hover,
        .sidebar-menu > li.active > a {
            background: #1e282c; color: #fff;
            text-decoration: none;
        }
        .sidebar-menu > li.active > a { border-left-color: #3c8dbc; }
        .sidebar-menu > li > a > i    { width: 20px; margin-right: 9px; text-align: center; }
        .arrow-icon { margin-left: auto; transition: transform .3s; }
        .treeview.menu-open > a .arrow-icon { transform: rotate(-90deg); }

        /* Submenu */
        .treeview-menu {
            list-style: none; padding: 0; margin: 0;
            background: #2c3b41; display: none;
        }
        .treeview.menu-open > .treeview-menu { display: block; }
        .treeview-menu > li > a {
            display: flex; align-items: center;
            padding: 8px 15px 8px 36px;
            color: #8aa4af; font-size: 13px;
            text-decoration: none;
            transition: all .2s;
        }
        .treeview-menu > li > a:hover,
        .treeview-menu > li.active > a { color: #fff; background: #1e282c; text-decoration: none; }
        .treeview-menu > li > a > i    { margin-right: 7px; font-size: 11px; }

        /* ============================================
           TOP NAVBAR
        ============================================ */
        .main-header {
            position: fixed; top: 0; right: 0;
            left: 220px;
            height: 50px;
            background: #3c8dbc;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 15px;
            z-index: 800;
            box-shadow: 0 2px 4px rgba(0,0,0,.2);
            transition: left .3s;
        }
        .sidebar-toggle-btn {
            background: none; border: none; color: #fff;
            font-size: 18px; cursor: pointer;
            padding: 5px 10px; border-radius: 3px;
            transition: background .2s;
        }
        .sidebar-toggle-btn:hover { background: rgba(0,0,0,.15); }
        .close-app-btn {
            background: rgba(255,255,255,.2); border: none;
            color: #fff; width: 32px; height: 32px;
            border-radius: 3px; font-size: 14px;
            cursor: pointer; transition: background .2s;
        }
        .close-app-btn:hover { background: rgba(0,0,0,.2); }

        /* ============================================
           CONTENT WRAPPER
        ============================================ */
        .content-wrapper {
            margin-left: 220px;
            margin-top: 50px;
            padding: 20px;
            min-height: calc(100vh - 50px);
            transition: margin-left .3s;
        }

        /* Page header */
        .content-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .content-header h1 { font-size: 22px; font-weight: 700; color: #444; margin: 0; }
        .breadcrumb { background: none; padding: 0; margin: 0; font-size: 13px; }
        .breadcrumb > li + li:before { content: "/ "; color: #999; }
        .breadcrumb a { color: #3c8dbc; }
        .breadcrumb .active { color: #777; }

        /* ============================================
           DASHBOARD SMALL BOXES
        ============================================ */
        .small-box {
            border-radius: 4px; position: relative;
            display: block; margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,.15);
            overflow: hidden; color: #fff;
        }
        .small-box .inner { padding: 15px 15px 8px 15px; }
        .small-box .inner h3 { font-size: 34px; font-weight: 700; margin: 0 0 4px; line-height: 1; }
        .small-box .inner p  { font-size: 13px; font-weight: 600; letter-spacing: .5px; margin: 0; }
        .small-box .icon {
            position: absolute; top: 8px; right: 10px;
            font-size: 60px; opacity: .2;
            transition: all .3s;
        }
        .small-box:hover .icon { font-size: 68px; opacity: .3; }
        .small-box-footer {
            display: block; background: rgba(0,0,0,.1);
            padding: 4px 10px; color: #fff; font-size: 13px;
            text-decoration: none; transition: background .2s;
        }
        .small-box-footer:hover { background: rgba(0,0,0,.2); color: #fff; text-decoration: none; }

        .bg-red    { background: #dd4b39; }
        .bg-yellow { background: #f39c12; }
        .bg-green  { background: #00a65a; }
        .bg-blue   { background: #0073b7; }

        /* ============================================
           BOX
        ============================================ */
        .box {
            background: #fff; border-radius: 4px;
            border-top: 3px solid #3c8dbc;
            box-shadow: 0 1px 4px rgba(0,0,0,.1); margin-bottom: 20px;
        }
        .box-primary { border-top-color: #3c8dbc; }
        .box-warning  { border-top-color: #f39c12; }
        .box-success  { border-top-color: #00a65a; }
        .box-info     { border-top-color: #00c0ef; }
        .box-danger   { border-top-color: #dd4b39; }

        .box-header {
            padding: 12px 15px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid #f4f4f4;
        }
        .box-title { font-size: 15px; font-weight: 600; color: #444; margin: 0; }
        .box-body  { padding: 15px; }

        /* ============================================
           TABLE
        ============================================ */
        .table > thead > tr > th {
            background: #3c8dbc; color: #fff;
            font-size: 13px; border: none;
        }

        /* ============================================
           ACTION BAR
        ============================================ */
        .action-bar {
            margin-top: 5px;
            display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 10px;
        }

        /* ============================================
           SIDEBAR COLLAPSE
        ============================================ */
        body.sidebar-collapse .main-sidebar  { width: 0; overflow: hidden; }
        body.sidebar-collapse .main-header   { left: 0; }
        body.sidebar-collapse .content-wrapper { margin-left: 0; }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 767px) {
            .main-sidebar   { width: 0; overflow: hidden; }
            .main-header    { left: 0; }
            .content-wrapper { margin-left: 0; }
            body.sidebar-open .main-sidebar { width: 220px; overflow-y: auto; }
        }
    </style>
</head>
<body>

<!-- ===================== SIDEBAR ===================== -->
<aside class="main-sidebar" id="mainSidebar">

    <div class="sidebar-brand">
        <div class="brand-icon">G</div>
        <span class="brand-text">GKJ PENARUBAN</span>
    </div>

    <div class="sidebar-user">
        <div class="user-img"><i class="fa fa-user"></i></div>
        <div>
            <span class="user-name"><?= esc(session()->get('nama') ?? 'Admin') ?></span>
            <span class="user-role"><?= esc(ucfirst(session()->get('role') ?? 'Administrator')) ?></span>
        </div>
    </div>

    <ul class="sidebar-menu">

        <li class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <a href="<?= base_url('dashboard') ?>">
                <i class="fa fa-home"></i><span>Dashboard</span>
            </a>
        </li>

        <!-- JADWAL IBADAH -->
        <li class="treeview <?= in_array($activePage ?? '', ['jadwal-ibadah','jadwal-tambah']) ? 'menu-open active' : '' ?>">
            <a href="#">
                <i class="fa fa-calendar"></i>
                <span>Jadwal Ibadah</span>
                <i class="fa fa-angle-left arrow-icon"></i>
            </a>
            <ul class="treeview-menu">
                <li class="<?= ($activePage ?? '') === 'jadwal-ibadah' ? 'active' : '' ?>">
                    <a href="<?= base_url('jadwal') ?>">
                        <i class="fa fa-circle-o"></i> Data Jadwal
                    </a>
                </li>
                <li class="<?= ($activePage ?? '') === 'jadwal-tambah' ? 'active' : '' ?>">
                    <a href="<?= base_url('jadwal/tambah') ?>">
                        <i class="fa fa-circle-o"></i> Tambah Jadwal
                    </a>
                </li>
            </ul>
        </li>

        <!-- PROGRAM KERJA -->
        <li class="treeview <?= in_array($activePage ?? '', ['program-kerja','komisi','program-tambah']) ? 'menu-open active' : '' ?>">
            <a href="#">
                <i class="fa fa-tasks"></i>
                <span>Program Kerja</span>
                <i class="fa fa-angle-left arrow-icon"></i>
            </a>
            <ul class="treeview-menu">
                <li class="<?= ($activePage ?? '') === 'komisi' ? 'active' : '' ?>">
                    <a href="<?= base_url('komisi') ?>">
                        <i class="fa fa-circle-o"></i> Data Komisi
                    </a>
                </li>
                <li class="<?= ($activePage ?? '') === 'program-kerja' ? 'active' : '' ?>">
                    <a href="<?= base_url('program') ?>">
                        <i class="fa fa-circle-o"></i> Program Kerja
                    </a>
                </li>
                <li class="<?= ($activePage ?? '') === 'program-tambah' ? 'active' : '' ?>">
                    <a href="<?= base_url('program/tambah') ?>">
                        <i class="fa fa-circle-o"></i> Tambah Program
                    </a>
                </li>
            </ul>
        </li>

        <!-- WHATSAPP GATEWAY -->
        <li class="<?= ($activePage ?? '') === 'whatsapp' ? 'active' : '' ?>">
            <a href="<?= base_url('whatsapp') ?>">
                <i class="fa fa-whatsapp"></i><span>WhatsApp Gateway</span>
            </a>
        </li>

        <!-- PENGUMUMAN -->
        <li class="<?= ($activePage ?? '') === 'pengumuman' ? 'active' : '' ?>">
            <a href="<?= base_url('pengumuman') ?>">
                <i class="fa fa-bullhorn"></i><span>Pengumuman</span>
            </a>
        </li>

        <!-- BACKUP -->
        <li class="<?= ($activePage ?? '') === 'backup' ? 'active' : '' ?>">
            <a href="<?= base_url('backup') ?>">
                <i class="fa fa-database"></i><span>Backup</span>
            </a>
        </li>

        <!-- KELUAR -->
        <li>
            <a href="<?= base_url('logout') ?>"
               onclick="return confirm('Yakin ingin keluar?')">
                <i class="fa fa-sign-out"></i><span>Keluar</span>
            </a>
        </li>

    </ul>
</aside>

<!-- ===================== TOP NAVBAR ===================== -->
<header class="main-header" id="mainHeader">
    <button class="sidebar-toggle-btn" id="sidebarToggle">
        <i class="fa fa-bars"></i>
    </button>
    <div>
        <button class="close-app-btn" title="Tutup"
                onclick="if(confirm('Keluar dari aplikasi?')) window.close()">
            <i class="fa fa-times"></i>
        </button>
    </div>
</header>

<!-- ===================== CONTENT ===================== -->
<div class="content-wrapper" id="contentWrapper">

    <!-- Breadcrumb -->
    <div class="content-header">
        <h1><?= isset($pageTitle) ? strtoupper(esc($pageTitle)) : 'DASHBOARD' ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="active"><?= esc($pageTitle ?? 'Dashboard') ?></li>
        </ol>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible">
            <button class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check"></i> <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-warning"></i> <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button class="close" data-dismiss="alert">&times;</button>
            <ul style="margin:0;padding-left:18px;">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Page Content -->
    <?= view($contentView, get_defined_vars()) ?>

</div><!-- /.content-wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 3 JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script>
$(function () {
    // Sidebar toggle
    $('#sidebarToggle').on('click', function () {
        if ($(window).width() <= 767) {
            $('body').toggleClass('sidebar-open');
        } else {
            $('body').toggleClass('sidebar-collapse');
            localStorage.setItem('sb', $('body').hasClass('sidebar-collapse') ? '1' : '0');
        }
    });

    // Restore state
    if ($(window).width() > 767 && localStorage.getItem('sb') === '1') {
        $('body').addClass('sidebar-collapse');
    }

    // Treeview toggle
    $('.treeview > a').on('click', function (e) {
        e.preventDefault();
        var $li = $(this).closest('.treeview');
        var open = $li.hasClass('menu-open');
        $('.treeview.menu-open').not($li).removeClass('menu-open');
        $li.toggleClass('menu-open', !open);
    });

    // Outside click (mobile)
    $(document).on('click', function (e) {
        if ($(window).width() <= 767 && $('body').hasClass('sidebar-open')) {
            if (!$(e.target).closest('#mainSidebar, #sidebarToggle').length) {
                $('body').removeClass('sidebar-open');
            }
        }
    });

    // Auto-dismiss alerts
    setTimeout(function () { $('.alert-dismissible').fadeOut(600); }, 4000);
});
</script>
</body>
</html>

<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Auth ──────────────────────────────────────────────
$routes->get('/',               'Auth::index');
$routes->get('login',           'Auth::index');
$routes->post('login',          'Auth::login');
$routes->get('logout',          'Auth::logout');

// ── Dashboard ─────────────────────────────────────────
$routes->get('dashboard',       'Dashboard::index');

// ── Jadwal Ibadah ─────────────────────────────────────
$routes->get('jadwal',          'JadwalIbadah::index');
$routes->get('jadwal/tambah',   'JadwalIbadah::tambah');
$routes->post('jadwal/simpan',  'JadwalIbadah::simpan');
$routes->get('jadwal/edit/(:num)',   'JadwalIbadah::edit/$1');
$routes->post('jadwal/update/(:num)','JadwalIbadah::update/$1');
$routes->get('jadwal/hapus/(:num)',  'JadwalIbadah::hapus/$1');

// ── Program Kerja ─────────────────────────────────────
$routes->get('komisi',          'ProgramKerja::komisi');
$routes->post('komisi/simpan',  'ProgramKerja::komisiSimpan');
$routes->get('komisi/edit/(:num)',    'ProgramKerja::komisiEdit/$1');
$routes->post('komisi/update/(:num)','ProgramKerja::komisiUpdate/$1');
$routes->get('komisi/hapus/(:num)',   'ProgramKerja::komisiHapus/$1');

$routes->get('program',         'ProgramKerja::index');
$routes->get('program/tambah',  'ProgramKerja::tambah');
$routes->post('program/simpan', 'ProgramKerja::simpan');
$routes->get('program/edit/(:num)',   'ProgramKerja::edit/$1');
$routes->post('program/update/(:num)','ProgramKerja::update/$1');
$routes->get('program/hapus/(:num)',  'ProgramKerja::hapus/$1');

// ── WhatsApp Gateway ──────────────────────────────────
$routes->get('whatsapp',        'Whatsapp::index');
$routes->post('whatsapp/kirim', 'Whatsapp::kirim');

// ── Pengumuman ────────────────────────────────────────
$routes->get('pengumuman',      'Pengumuman::index');
$routes->get('pengumuman/tambah',   'Pengumuman::tambah');
$routes->post('pengumuman/simpan',  'Pengumuman::simpan');
$routes->get('pengumuman/edit/(:num)',   'Pengumuman::edit/$1');
$routes->post('pengumuman/update/(:num)','Pengumuman::update/$1');
$routes->get('pengumuman/hapus/(:num)',  'Pengumuman::hapus/$1');

// ── Backup ────────────────────────────────────────────
$routes->get('backup',          'Backup::index');
$routes->post('backup/proses',  'Backup::proses');

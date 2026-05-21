# Website KEGIATAN PELAYANAN GEREJA menggunakan CodeIgniter 4 Framework
Aplikasi website kegiatan pelyanan gereja dari (https://kegiatanpelayanangereja.org/) dengan berbagai fitur yang kiranya bermanfaat. 

Spesifikasi Teknis Source Code
Website ini dikembangkan dengan beberapa spesifikasi:
1. Dikembangan dengan Codeigniter 4. Pastikan teman-teman membaca Server Requirements dari CI4 ini.
2. Datatables dan plugin export

# Fitur-fitur Website meliputi:

## HALAMAN FRONT END:

1. Halaman Beranda/Homepage
2. Background GKJ Penaruban
3. Halaman utama (jadwal ibadah, proker, convert pdf, indeks)
4. Halaman profile (Profil user, dan admin)
5. Halaman Jadwalibadah
6. Halaman Proker (masing-masing komisi)
12. Login admin, dan user
13. Pendaftaran 

## HALAMAN BACK END:
1. Login
2. Halaman update profile dan ganti password
3. Halaman Dashboard
4. Halaman kelola pendaftar
5. Halaman kelola komisinya
6. Halaman kelola Galeri Komisinya
7. Halaman kelola jadwal ibadah (jadwal ibadah minggu, dan tukar mimbar klasis)
8. Halaman kelola proker, dari masing-masing komisinya
10. Halaman kelola upload/download file
11. Halaman kelola dokumentasi
12. Dan fitur lainnya

## Mengakses Halaman Website dan Login ke Admin
1. Buka browser Anda
2. Ketik alamat http://kegiatanpelayanangereja.org/
3. Untuk Login ke halaman Back End, silakan buka http://kegiatanpelayanangereja.org/login
4. Username admin: admin
6. Password admin: 123
5. Untuk Login user dan pendaftar, silahkan buka http://kegiatanpelayanangereja.org/signin

Catatan : Beberapa fitur masih dalam tahap pengembangan, dan mungkin belum bekerja dengan baik

# CodeIgniter 4 Framework

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds the distributable version of the framework.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

The user guide corresponding to the latest version of the framework can be found
[here](https://codeigniter4.github.io/userguide/).

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Contributing

We welcome contributions from the community.

Please read the [*Contributing to CodeIgniter*](https://github.com/codeigniter4/CodeIgniter4/blob/develop/CONTRIBUTING.md) section in the development repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

# GKJ PENARUBAN
## Sistem Informasi Kegiatan Pelayanan Gereja

Dibangun dengan **CodeIgniter 4** + **Bootstrap 3** + **Font Awesome 4**

---

## Struktur Folder

```
gkj_penaruban/
├── app/
│   ├── Config/
│   │   ├── App.php          ← Konfigurasi baseURL
│   │   ├── Database.php     ← Konfigurasi database
│   │   ├── Filters.php      ← Auth filter
│   │   └── Routes.php       ← Semua route
│   ├── Controllers/
│   │   ├── Auth.php         ← Login / Logout
│   │   ├── Dashboard.php    ← Halaman utama
│   │   ├── JadwalIbadah.php ← CRUD Jadwal Ibadah
│   │   ├── ProgramKerja.php ← CRUD Komisi & Program Kerja
│   │   ├── Pengumuman.php   ← CRUD Pengumuman
│   │   ├── Whatsapp.php     ← WhatsApp Gateway
│   │   └── Backup.php       ← Backup Database
│   ├── Filters/
│   │   └── AuthFilter.php   ← Proteksi halaman
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── KomisiModel.php
│   │   ├── JadwalIbadahModel.php
│   │   ├── ProgramKerjaModel.php
│   │   ├── PengumumanModel.php
│   │   └── WaLogModel.php
│   ├── Database/
│   │   ├── Migrations/      ← Migration tabel
│   │   └── Seeds/           ← Data awal
│   └── Views/
│       ├── layouts/main.php ← Template utama (sidebar + navbar)
│       ├── auth/login.php
│       ├── dashboard/
│       ├── jadwal_ibadah/
│       ├── program_kerja/
│       ├── pengumuman/
│       ├── whatsapp/
│       └── backup/
├── public/                  ← Document root server
│   ├── index.php
│   └── .htaccess
├── .env                     ← Konfigurasi environment
├── database.sql             ← SQL setup database
├── spark                    ← CLI tool CI4
└── composer.json
```

---

## Cara Setup

### 1. Import Database
Buka **phpMyAdmin** → Import file `database.sql`

Atau via terminal:
```bash
mysql -u root -p < database.sql
```

### 2. Konfigurasi `.env`
Edit file `.env` jika perlu:
```env
app.baseURL = 'http://localhost/SERASI/gkj_penaruban/public/'
database.default.hostname = localhost
database.default.database = gkj_penaruban
database.default.username = root
database.default.password = 
```

### 3. Aktifkan mod_rewrite (XAMPP)
Pastikan `mod_rewrite` aktif di Apache XAMPP.

### 4. Akses Aplikasi
```
http://localhost/SERASI/gkj_penaruban/public/
```

### 5. Login Default
| Username | Password  | Role  |
|----------|-----------|-------|
| admin    | admin123  | Admin |

---

## Fitur

| Menu | Keterangan |
|------|------------|
| **Dashboard** | Statistik: jadwal, program, komisi, pengumuman |
| **Jadwal Ibadah** | CRUD jadwal ibadah per komisi/bulan |
| **Program Kerja** | CRUD komisi & program kerja per komisi |
| **WhatsApp Gateway** | Kirim pesan WA (perlu konfigurasi API) |
| **Pengumuman** | CRUD pengumuman gereja |
| **Backup** | Download backup database `.sql` |
| **Keluar** | Logout sesi |

---

## CLI Commands (Spark)

```bash
# Migration
php spark migrate

# Seeder
php spark db:seed GkjSeeder

# Jalankan server development
php spark serve
```

---

## Konfigurasi WhatsApp Gateway
Edit `app/Controllers/Whatsapp.php` dan tambahkan integrasi API pilihan:
- **Fonnte**: https://fonnte.com
- **WA-Web**: https://github.com/pedroslopez/whatsapp-web.js
- **CallMeBot**: https://www.callmebot.com

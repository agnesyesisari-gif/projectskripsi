-- Database: db_gereja

CREATE TABLE jadwal_ibadah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_ibadah VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    tempat VARCHAR(100) NOT NULL,
    pemimpin VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE program_kerja (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_program VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE,
    penanggung_jawab VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif', 'selesai', 'draft') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'pengurus', 'jemaat') DEFAULT 'jemaat',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default (password: )
INSERT INTO users (username, password, nama_lengkap, email, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin@gereja.com', 'admin');

CREATE TABLE anggota (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P'),
    alamat TEXT,
    no_telepon VARCHAR(20),
    email VARCHAR(100),
    tanggal_bergabung DATE,
    status_anggota ENUM('aktif', 'non-aktif', 'pindah') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
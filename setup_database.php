<?php
// setup_database.php
echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Database Gerbang Langit</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { color: green; padding: 10px; background: #d4edda; margin: 5px; border-radius: 5px; }
        .error { color: red; padding: 10px; background: #f8d7da; margin: 5px; border-radius: 5px; }
        .box { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔧 Setup Database Gerbang Langit</h1>";

// Koneksi ke MySQL
$conn = new mysqli("localhost", "root", "");

if ($conn->connect_error) {
    die("<div class='error'>❌ MySQL tidak aktif!<br>1. Buka XAMPP Control Panel<br>2. Klik START pada MySQL<br>3. Refresh halaman ini</div>");
}

echo "<div class='success'>✅ MySQL Connected</div>";

// Buat database
$sql = "CREATE DATABASE IF NOT EXISTS gerbang_langit 
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<div class='success'>✅ Database 'gerbang_langit' created</div>";
} else {
    echo "<div class='error'>❌ Error: " . $conn->error . "</div>";
}

// Pilih database
$conn->select_db("gerbang_langit");

// SQL untuk membuat tabel
$sql_tables = "
-- Tabel packages
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    deskripsi TEXT,
    gambar VARCHAR(500),
    badge VARCHAR(50),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel bookings
CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(50) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    paket VARCHAR(255) NOT NULL,
    harga DECIMAL(12,2) DEFAULT 0,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    jumlah INT DEFAULT 1,
    total DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel facilities
CREATE TABLE IF NOT EXISTS facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    kategori ENUM('akomodasi','makanan','aktivitas','keamanan','umum') NOT NULL,
    lokasi VARCHAR(255),
    deskripsi TEXT,
    status ENUM('active','inactive','maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel testimonials
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    rating DECIMAL(2,1) DEFAULT 5.0,
    testimoni TEXT NOT NULL,
    status ENUM('active','pending','inactive') DEFAULT 'pending',
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel gallery
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    album ENUM('pemandangan','aktivitas','fasilitas','lainnya') DEFAULT 'pemandangan',
    gambar VARCHAR(500) NOT NULL,
    status ENUM('active','pending') DEFAULT 'active',
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel admin_users
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') DEFAULT 'admin',
    status ENUM('active','inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

// Eksekusi SQL
if ($conn->multi_query($sql_tables)) {
    echo "<div class='success'>✅ Semua tabel berhasil dibuat</div>";
    
    // Tunggu semua query selesai
    while ($conn->next_result()) {;}
} else {
    echo "<div class='error'>❌ Error membuat tabel: " . $conn->error . "</div>";
}

// Insert data admin (password: admin123)
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);
$sql_admin = "INSERT IGNORE INTO admin_users (username, password, nama_lengkap, email) 
              VALUES ('admin', '$password_hash', 'Administrator', 'admin@gerbanglangit.com')";
if ($conn->query($sql_admin)) {
    echo "<div class='success'>✅ Data admin berhasil ditambahkan</div>";
}

// Insert data contoh
$sql_sample = "
INSERT IGNORE INTO packages (nama, harga, deskripsi, gambar, badge) VALUES
('Trip Bromo Sunrise', 550000, 'Jeep Offroad • Guide Lokal • Makan Pagi', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4', 'BESTSELLER'),
('Camping Gerbang Langit', 250000, 'Tenda Premium • Api Unggun • Breakfast', 'https://images.unsplash.com/photo-1504851149312-7a075b496cc7', 'POPULAR');

INSERT IGNORE INTO facilities (nama, kategori, deskripsi) VALUES
('Gazebo Panorama', 'akomodasi', 'Gazebo dengan view 360°'),
('Warung Makan', 'makanan', 'Makanan tradisional khas Lombok');

INSERT IGNORE INTO testimonials (nama, rating, testimoni, status, tanggal) VALUES
('Budi Santoso', 5, 'Pengalaman luar biasa!', 'active', '2024-01-15'),
('Siti Rahayu', 4.5, 'Pelayanan sangat ramah', 'active', '2024-01-14');
";

if ($conn->multi_query($sql_sample)) {
    echo "<div class='success'>✅ Data contoh berhasil ditambahkan</div>";
    while ($conn->next_result()) {;}
}

// Tampilkan status
echo "<div class='box'>";
echo "<h3>📊 Status Database:</h3>";

$tables = $conn->query("SHOW TABLES");
$table_count = 0;
while ($row = $tables->fetch_array()) {
    $table_count++;
    $table_name = $row[0];
    
    // Hitung jumlah data
    $count_result = $conn->query("SELECT COUNT(*) as count FROM $table_name");
    $count_row = $count_result->fetch_assoc();
    
    echo "✅ <b>$table_name</b>: " . $count_row['count'] . " data<br>";
}

echo "<hr>";
echo "<h3>🔑 Login Admin:</h3>";
echo "<p>Username: <code><b>admin</b></code></p>";
echo "<p>Password: <code><b>admin123</b></code></p>";
echo "<p><a href='login.html' style='background:#4CAF50;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;display:inline-block;'>→ Login ke Admin Panel</a></p>";
echo "</div>";

$conn->close();
?>
</body>
</html>
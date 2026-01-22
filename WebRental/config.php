<?php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_management');

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Set mode error PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Tangani error koneksi
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi helper untuk redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

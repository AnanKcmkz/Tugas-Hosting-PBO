<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: Login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = intval($_POST['car_id']);
    $stmt = $pdo->prepare("UPDATE cars SET available = NOT available WHERE id = ?");
    $stmt->execute([$car_id]);
    $_SESSION['success'] = "Status ketersediaan mobil berhasil diubah!";
}

header("Location: admin_cars.php");
exit();
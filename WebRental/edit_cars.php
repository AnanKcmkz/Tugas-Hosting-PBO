<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: Login.php");
    exit();
}

// Ambil data mobil berdasarkan ID
$car_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika mobil tidak ditemukan, redirect
if (!$car) {
    header("Location: admin_cars.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price_per_day = intval($_POST['price_per_day']);
    $seats = intval($_POST['seats']);
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $luggage = $_POST['luggage'];
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE cars SET name = ?, category = ?, price_per_day = ?, seats = ?, transmission = ?, fuel_type = ?, luggage = ?, available = ? WHERE id = ?");
    $stmt->execute([$name, $category, $price_per_day, $seats, $transmission, $fuel_type, $luggage, $available, $car_id]);

    $_SESSION['success'] = "Mobil berhasil diupdate!";
    header("Location: admin_cars.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mobil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Gunakan style yang sama dengan admin_cars.php */
        :root{
            --purple:#5b2df5;
            --purple-2:#6f46ff;
            --muted:#6b7280;
            --card:#ffffff;
            --bg:#f8f8ff;
            --accent:#ffb238;
            --success:#10b981;
            --warning:#f59e0b;
            --danger:#ef4444;
            --radius:22px;
            --container:1100px;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background:var(--bg);
            color:#0f172a;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }
        .wrap{max-width:var(--container);margin:40px auto;padding:24px}

        /* NAVBAR (sama dengan Home2.php) */
        nav {
            width:100%;
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:18px 32px;
            position:sticky;
            top:0;
            z-index:50;
            background:white;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
        }
        
        .logo {
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:800;
            font-size:20px;
            color:#111;
        }
        
        nav ul {
            display:flex;
            gap:30px;
            list-style:none;
            margin:0;
            padding:0;
            font-weight:500;
        }
        
        nav a {
            text-decoration:none;
            color:#111;
            transition: color 0.3s;
        }
        
        nav a:hover {
            color: var(--purple);
        }
        
        .user-info {
            display:flex;
            align-items:center;
            gap:15px;
        }
        
        .user-info span {
            color:#5b2df5;
        }
        
        .logout-btn {
            padding:10px 18px;
            background:#5b2df5;
            color:white;
            border-radius:10px;
            font-weight:600;
            text-decoration:none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background:#4a1fd6;
        }

        /* Form */
        .form-container {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(16,24,40,0.06);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #e6e7f2;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--purple);
            color: white;
        }

        .btn-primary:hover {
            background: var(--purple-2);
        }

        .btn-secondary {
            background: var(--muted);
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .checkbox-group {
            flex-direction: row;
            align-items: center;
        }

        .checkbox-group input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <span>🚗</span> <span>RentalCar</span>
        </div>
        
        <ul>
            <li><a href="Home2.php">Home</a></li>
            <li><a href="MenuPilihCar.php">All Cars</a></li>
            <li><a href="ContactUs.php">Contact</a></li>
            <li><a href="admin_cars.php">Admin</a></li>
        </ul>

        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="wrap">
        <h1>Edit Mobil</h1>

        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Mobil</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($car['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <option value="Luxury" <?php echo $car['category'] == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                            <option value="Premium" <?php echo $car['category'] == 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Sport" <?php echo $car['category'] == 'Sport' ? 'selected' : ''; ?>>Sport</option>
                            <option value="Family" <?php echo $car['category'] == 'Family' ? 'selected' : ''; ?>>Family</option>
                            <option value="SUV" <?php echo $car['category'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                            <option value="Hatchback" <?php echo $car['category'] == 'Hatchback' ? 'selected' : ''; ?>>Hatchback</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price_per_day">Harga per Hari (IDR)</label>
                        <input type="number" id="price_per_day" name="price_per_day" value="<?php echo $car['price_per_day']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="seats">Kursi</label>
                        <input type="number" id="seats" name="seats" value="<?php echo $car['seats']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="transmission">Transmisi</label>
                        <select id="transmission" name="transmission" required>
                            <option value="Automatic" <?php echo $car['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                            <option value="Manual" <?php echo $car['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fuel_type">Bahan Bakar</label>
                        <input type="text" id="fuel_type" name="fuel_type" value="<?php echo htmlspecialchars($car['fuel_type']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="luggage">Bagasi</label>
                        <input type="text" id="luggage" name="luggage" value="<?php echo htmlspecialchars($car['luggage']); ?>" required>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="available" name="available" <?php echo $car['available'] ? 'checked' : ''; ?>>
                        <label for="available">Tersedia</label>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="admin_cars.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Update Mobil</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
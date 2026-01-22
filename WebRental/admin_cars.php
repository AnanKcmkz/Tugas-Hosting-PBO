<?php
session_start();
require_once 'config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit();
}

// Handle CRUD Operations
$message = '';
$message_type = '';

// CREATE - Tambah mobil baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price_per_day = $_POST['price_per_day'];
    $seats = $_POST['seats'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $luggage = $_POST['luggage'];
    $available = isset($_POST['available']) ? 1 : 0;

    // Handle file upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image_name);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO cars (name, category, price_per_day, image, seats, transmission, fuel_type, luggage, available) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price_per_day, $image_name, $seats, $transmission, $fuel_type, $luggage, $available]);
        
        $message = "Mobil berhasil ditambahkan!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// UPDATE - Edit mobil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_car'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price_per_day = $_POST['price_per_day'];
    $seats = $_POST['seats'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $luggage = $_POST['luggage'];
    $available = isset($_POST['available']) ? 1 : 0;

    // Handle file upload
    $image_name = $_POST['current_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Hapus gambar lama jika ada
        if ($image_name && file_exists('images/' . $image_name)) {
            unlink('images/' . $image_name);
        }
        $image_name = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image_name);
    }

    try {
        $stmt = $pdo->prepare("UPDATE cars SET name=?, category=?, price_per_day=?, image=?, seats=?, transmission=?, fuel_type=?, luggage=?, available=? WHERE id=?");
        $stmt->execute([$name, $category, $price_per_day, $image_name, $seats, $transmission, $fuel_type, $luggage, $available, $id]);
        
        $message = "Mobil berhasil diupdate!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// DELETE - Hapus mobil
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        // Hapus gambar jika ada
        $stmt = $pdo->prepare("SELECT image FROM cars WHERE id = ?");
        $stmt->execute([$id]);
        $car = $stmt->fetch();
        
        if ($car && $car['image'] && file_exists('images/' . $car['image'])) {
            unlink('images/' . $car['image']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = "Mobil berhasil dihapus!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Ambil semua data mobil
$stmt = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data mobil untuk edit
$edit_car = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_car = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - RentalCar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #5b2df5;
            --purple-2: #6f46ff;
            --muted: #6b7280;
            --card: #ffffff;
            --bg: #f8f8ff;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #0f172a;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 24px;
            color: var(--purple);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            padding: 8px 16px;
            background: var(--purple);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: var(--purple-2);
        }
        
        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: var(--purple);
            border-bottom-color: var(--purple);
        }
        
        /* Forms */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--purple);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
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
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-danger {
            background: var(--error);
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-available {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-unavailable {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="nav">
            <div class="logo">
                <span>🚗</span>
                <span>RentalCar - Admin Panel</span>
            </div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</span>
                <a href="Home2.php" class="btn btn-secondary">Back to Home</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="showTab('cars')">Manage Cars</button>
            <button class="tab" onclick="showTab('addCar')"><?php echo $edit_car ? 'Edit Car' : 'Add New Car'; ?></button>
        </div>

        <!-- Add/Edit Car Form -->
        <div id="addCar" class="tab-content" style="display: <?php echo $edit_car ? 'block' : 'none'; ?>;">
            <div class="form-container">
                <h2><?php echo $edit_car ? 'Edit Car' : 'Add New Car'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_car): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_car['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo $edit_car['image']; ?>">
                        <input type="hidden" name="edit_car" value="1">
                    <?php else: ?>
                        <input type="hidden" name="add_car" value="1">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Car Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $edit_car ? $edit_car['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Luxury" <?php echo ($edit_car && $edit_car['category'] == 'Luxury') ? 'selected' : ''; ?>>Luxury</option>
                            <option value="Premium" <?php echo ($edit_car && $edit_car['category'] == 'Premium') ? 'selected' : ''; ?>>Premium</option>
                            <option value="Sport" <?php echo ($edit_car && $edit_car['category'] == 'Sport') ? 'selected' : ''; ?>>Sport</option>
                            <option value="Family" <?php echo ($edit_car && $edit_car['category'] == 'Family') ? 'selected' : ''; ?>>Family</option>
                            <option value="SUV" <?php echo ($edit_car && $edit_car['category'] == 'SUV') ? 'selected' : ''; ?>>SUV</option>
                            <option value="Hatchback" <?php echo ($edit_car && $edit_car['category'] == 'Hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price_per_day">Price per Day (IDR)</label>
                        <input type="number" id="price_per_day" name="price_per_day" value="<?php echo $edit_car ? $edit_car['price_per_day'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="seats">Number of Seats</label>
                        <input type="number" id="seats" name="seats" value="<?php echo $edit_car ? $edit_car['seats'] : '5'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission" required>
                            <option value="Automatic" <?php echo ($edit_car && $edit_car['transmission'] == 'Automatic') ? 'selected' : ''; ?>>Automatic</option>
                            <option value="Manual" <?php echo ($edit_car && $edit_car['transmission'] == 'Manual') ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <input type="text" id="fuel_type" name="fuel_type" value="<?php echo $edit_car ? $edit_car['fuel_type'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="luggage">Luggage Capacity</label>
                        <input type="text" id="luggage" name="luggage" value="<?php echo $edit_car ? $edit_car['luggage'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Car Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if ($edit_car && $edit_car['image']): ?>
                            <p>Current image: <?php echo $edit_car['image']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="available" name="available" <?php echo ($edit_car && $edit_car['available']) ? 'checked' : 'checked'; ?>>
                            <label for="available">Available for rental</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $edit_car ? 'Update Car' : 'Add Car'; ?></button>
                    
                    <?php if ($edit_car): ?>
                        <a href="admin_cars.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Cars List -->
        <div id="cars" class="tab-content" style="display: <?php echo $edit_car ? 'none' : 'block'; ?>;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price/Day</th>
                            <th>Seats</th>
                            <th>Transmission</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cars)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No cars found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <td>
                                        <?php if ($car['image'] && file_exists('images/' . $car['image'])): ?>
                                            <img src="images/<?php echo $car['image']; ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" class="car-image">
                                        <?php else: ?>
                                            <div style="width: 80px; height: 60px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: var(--muted); font-size: 12px;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($car['name']); ?></td>
                                    <td><?php echo htmlspecialchars($car['category']); ?></td>
                                    <td>IDR <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($car['seats']); ?></td>
                                    <td><?php echo htmlspecialchars($car['transmission']); ?></td>
                                    <td>
                                        <span class="status <?php echo $car['available'] ? 'status-available' : 'status-unavailable'; ?>">
                                            <?php echo $car['available'] ? 'Available' : 'Unavailable'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="admin_cars.php?edit=<?php echo $car['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="admin_cars.php?delete=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this car?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab and set active class
            document.getElementById(tabName).style.display = 'block';
            event.currentTarget.classList.add('active');
        }
        
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
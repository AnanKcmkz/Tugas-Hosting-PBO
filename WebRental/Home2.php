<?php
session_start();
require_once 'config.php';

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Fungsi untuk mendapatkan placeholder gambar berdasarkan kategori
function getCarPlaceholderImage($category, $name) {
    $baseUrl = "https://source.unsplash.com/featured/300x120/?";
    
    switch(strtolower($category)) {
        case 'luxury':
            return $baseUrl . "mercedes,s-class,luxury+car&sig=" . md5($name);
        case 'premium':
            return $baseUrl . "mercedes,premium+car&sig=" . md5($name);
        case 'sport':
            return $baseUrl . "bmw,sports+car&sig=" . md5($name);
        case 'family':
            return $baseUrl . "toyota,innova,family+car&sig=" . md5($name);
        case 'suv':
            return $baseUrl . "toyota,rush,suv&sig=" . md5($name);
        case 'hatchback':
            return $baseUrl . "toyota,yaris,hatchback&sig=" . md5($name);
        default:
            return $baseUrl . "car&sig=" . md5($name);
    }
}

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_car'])) {
        // Delete car
        $car_id = intval($_POST['car_id']);
        $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        $_SESSION['success'] = "Car deleted successfully!";
        header("Location: Home2.php");
        exit();
    }
    
    if (isset($_POST['toggle_availability'])) {
        // Toggle car availability
        $car_id = intval($_POST['car_id']);
        $stmt = $pdo->prepare("UPDATE cars SET available = NOT available WHERE id = ?");
        $stmt->execute([$car_id]);
        $_SESSION['success'] = "Car availability updated!";
        header("Location: Home2.php");
        exit();
    }
}

// Handle adding new car
if (isset($_GET['action']) && $_GET['action'] == 'add_car' && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    // Simple form untuk add car
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price_per_day = intval($_POST['price_per_day']);
        $seats = intval($_POST['seats']);
        $transmission = $_POST['transmission'];
        $fuel_type = $_POST['fuel_type'];
        $luggage = $_POST['luggage'];
        
        $stmt = $pdo->prepare("INSERT INTO cars (name, category, price_per_day, seats, transmission, fuel_type, luggage, available) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $category, $price_per_day, $seats, $transmission, $fuel_type, $luggage]);
        
        $_SESSION['success'] = "New car added successfully!";
        header("Location: Home2.php");
        exit();
    }
}

// Ambil data mobil dari database untuk ditampilkan
$stmt = $pdo->query("SELECT * FROM cars WHERE available = 1 LIMIT 6");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Jika tidak ada data mobil, gunakan data default
if (empty($cars)) {
    $cars = [
        ['id' => 1, 'name' => 'S-Class', 'category' => 'Luxury', 'price_per_day' => 1500000, 'image' => 's-class.jpg', 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Premium', 'luggage' => '3 bags'],
        ['id' => 2, 'name' => 'Mercedes B-Class', 'category' => 'Premium', 'price_per_day' => 1200000, 'image' => 'b-class.jpg', 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Premium', 'luggage' => '2 bags'],
        ['id' => 3, 'name' => 'BMW M5', 'category' => 'Sport', 'price_per_day' => 950000, 'image' => 'm5.jpg', 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Premium', 'luggage' => '2 bags'],
        ['id' => 4, 'name' => 'Toyota Innova', 'category' => 'Family', 'price_per_day' => 450000, 'image' => 'inova.png', 'seats' => 7, 'transmission' => 'Manual', 'fuel_type' => 'Pertamax', 'luggage' => '4 bags'],
        ['id' => 5, 'name' => 'Toyota Rush', 'category' => 'SUV', 'price_per_day' => 480000, 'image' => 'rush.jpg', 'seats' => 7, 'transmission' => 'Manual', 'fuel_type' => 'Pertamax', 'luggage' => '3 bags'],
        ['id' => 6, 'name' => 'Toyota Yaris', 'category' => 'Hatchback', 'price_per_day' => 350000, 'image' => 'yaris.png', 'seats' => 5, 'transmission' => 'Manual', 'fuel_type' => 'Pertalite', 'luggage' => '2 bags']
    ];
}

// Siapkan gambar untuk setiap mobil
foreach ($cars as &$car) {
    if (!empty($car['image']) && file_exists('images/' . $car['image'])) {
        $car['image_url'] = 'images/' . $car['image'];
    } else {
        $car['image_url'] = getCarPlaceholderImage($car['category'], $car['name']);
    }
}
unset($car); // Hapus reference
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Car Rental — Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
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
    html,body{height:100%}
    body{
      margin:0;
      font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background:var(--bg);
      color:#0f172a;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }
    .wrap{max-width:var(--container);margin:40px auto;padding:24px}

    /* NAVBAR */
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

    /* HERO */
    .hero{
      background:linear-gradient(135deg, var(--purple) 0%, var(--purple-2) 100%);
      border-radius:28px;
      color:white;
      padding:36px;
      position:relative;
      overflow:visible;
      display:flex;
      gap:32px;
      align-items:center;
    }
    .hero-left{flex:1}
    .eyebrow{display:inline-block;background:rgba(255,255,255,0.12);padding:6px 12px;border-radius:999px;font-weight:600;margin-bottom:14px}
    .hero h1{font-size:36px;margin:0 0 18px;line-height:1.05}
    .hero p{margin:0 0 18px;color:rgba(255,255,255,0.88)}
    
    /* Tombol yang disamakan */
    .cta, .book-btn, .btn-small, .mini-cta button {
      padding: 12px 24px;
      background: var(--purple);
      color: #fff;
      border-radius: 8px;
      text-align: center;
      text-decoration: none;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background 0.3s ease;
      display: inline-block;
    }
    
    .cta:hover, .book-btn:hover, .btn-small:hover, .mini-cta button:hover {
      background: var(--purple-2);
    }

    /* CRUD Buttons */
    .crud-buttons {
      display: flex;
      gap: 8px;
      margin-top: 10px;
      flex-wrap: wrap;
    }
    
    .btn-edit {
      padding: 6px 12px;
      background: var(--warning);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      transition: background 0.3s;
      flex: 1;
      text-align: center;
    }
    
    .btn-edit:hover {
      background: #d97706;
    }
    
    .btn-delete {
      padding: 6px 12px;
      background: var(--danger);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      transition: background 0.3s;
      flex: 1;
      text-align: center;
    }
    
    .btn-delete:hover {
      background: #dc2626;
    }
    
    .btn-toggle {
      padding: 6px 12px;
      background: var(--success);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      transition: background 0.3s;
      flex: 1;
      text-align: center;
    }
    
    .btn-toggle:hover {
      background: #059669;
    }

    /* Add Car Form */
    .add-car-form {
      background: white;
      border-radius: 14px;
      padding: 20px;
      margin: 20px 0;
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

    /* booking card */
    .booking{width:320px;background:var(--card);padding:18px;border-radius:16px;box-shadow:0 12px 30px rgba(19,11,62,0.12);color:#111;position:relative}
    .booking h3{margin:0 0 10px;font-size:18px}
    .field{display:flex;flex-direction:column;margin-bottom:12px}
    .field label{font-size:12px;color:var(--muted);margin-bottom:6px}
    .field input,.field select{padding:10px;border-radius:10px;border:1px solid #e6e7f2}

    /* icons row */
    .icons{display:flex;gap:36px;padding:24px 0 10px;justify-content:center}
    .icon-item{text-align:center}
    .icon-item h4{margin:10px 0 6px;font-size:15px}
    .icon-item p{margin:0;color:var(--muted);font-size:13px}

    /* main content */
    .main{display:flex;gap:40px;margin-top:22px}
    .main-left{flex:1}
    .featured-card{background:white;border-radius:14px;padding:18px;box-shadow:0 6px 20px rgba(16,24,40,0.06);display:flex;gap:18px}
    .featured-card img{width:260px;height:160px;object-fit:cover;border-radius:10px}
    .featured-list{flex:1}
    .featured-list ul{padding-left:18px;margin:0}
    .featured-list li{margin-bottom:12px;color:var(--muted)}

    /* catalogue */
    .catalogue{margin-top:28px}
    .card-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    .car-card{background:white;border-radius:12px;padding:12px;box-shadow:0 6px 18px rgba(16,24,40,0.04);transition: transform 0.3s, box-shadow 0.3s;}
    .car-card:hover{transform:translateY(-5px);box-shadow:0 12px 25px rgba(16,24,40,0.1)}
    .car-card img{width:100%;height:120px;object-fit:cover;border-radius:8px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:12px;}
    .car-card h5{margin:8px 0 4px;font-size:14px}
    .car-card p{margin:0;color:var(--muted);font-size:13px}
    .price-row{display:flex;justify-content:space-between;align-items:center;margin-top:10px}

    /* facts */
    .facts{margin-top:28px;background:linear-gradient(180deg,rgba(91,45,245,0.12),rgba(111,70,255,0.06));padding:22px;border-radius:14px;display:flex;gap:18px;align-items:center;justify-content:space-between}
    .fact{background:white;padding:12px;border-radius:12px;min-width:120px;text-align:center}
    .fact h3{margin:0;font-size:18px}
    .fact p{margin:4px 0 0;color:var(--muted)}

    /* CTA small */
    .mini-cta{margin-top:24px;background:var(--purple);color:white;padding:18px;border-radius:14px;display:flex;justify-content:space-between;align-items:center}

    /* Back to Top Button */
    .back-to-top {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      background: var(--purple);
      color: white;
      border: none;
      border-radius: 50%;
      cursor: pointer;
      font-size: 20px;
      display: none;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(91, 45, 245, 0.3);
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .back-to-top:hover {
      background: var(--purple-2);
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(91, 45, 245, 0.4);
    }

    .back-to-top.show {
      display: flex;
    }

    footer{margin-top:30px;padding:24px 0;color:var(--muted);display:flex;justify-content:space-between;align-items:center}
    footer .social{display:flex;gap:8px}

    /* Success Message */
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
    }
    
    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }

    @media(max-width:1000px){
      .card-grid{grid-template-columns:repeat(2,1fr)}
      .hero{flex-direction:column;align-items:flex-start}
      .booking{width:100%}
      .main{flex-direction:column}
    }
    @media(max-width:640px){
      .card-grid{grid-template-columns:repeat(1,1fr)}
      .wrap{padding:14px;margin:18px}
      .hero h1{font-size:26px}
      nav {
        flex-direction: column;
        gap: 15px;
        padding: 15px 20px;
      }
      
      nav ul {
        gap: 15px;
      }
      
      /* Responsive untuk back to top button */
      .back-to-top {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
        font-size: 18px;
      }
      
      .crud-buttons {
        flex-direction: column;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav>
    <div class="logo">
      <span>🚗</span> <span>RentalCar</span>
    </div>
    
    <ul>
      <li><a href="Home2.php">Home</a></li>
      <li><a href="MenuPilihCar.php">All Cars</a></li>
      <li><a href="ContactUs.php">Contact</a></li>
    </ul>

    <div class="user-info">
      <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <div class="wrap">
    <!-- Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <div class="hero">
      <div class="hero-left">
        <div class="eyebrow">Experience</div>
        <h1>Experience the road<br/> like never before</h1>
        <p>Premium car rentals for any journey. Fast booking, flexible pickup, and 24/7 support.</p>
        <a class="cta" href="#catalog">Book your ride</a>
      </div>

      <div class="booking" aria-label="booking form">
        <h3>Book your car</h3>
        <div class="field"><label>Pick-up</label><input type="text" placeholder="Select date"/></div>
        <div class="field"><label>Drop-off</label><input type="text" placeholder="Select date"/></div>
        <div class="field"><label>Car type</label>
          <select>
            <option>S-Class</option>
            <option>SUV</option>
            <option>Hatchback</option>
          </select>
        </div>
        <button class="book-btn">Search car</button>
      </div>
    </div>

    <div class="icons">
      <div class="icon-item">
        <div style="width:56px;height:56px;border-radius:12px;background:var(--purple);display:flex;align-items:center;justify-content:center;margin:0 auto">
          <span style="color:white;font-size:24px">📍</span>
        </div>
        <h4>Location</h4>
        <p>Across cities</p>
      </div>
      <div class="icon-item">
        <div style="width:56px;height:56px;border-radius:12px;background:var(--purple);display:flex;align-items:center;justify-content:center;margin:0 auto">
          <span style="color:white;font-size:24px">💺</span>
        </div>
        <h4>Comfort</h4>
        <p>Premium vehicles</p>
      </div>
      <div class="icon-item">
        <div style="width:56px;height:56px;border-radius:12px;background:var(--purple);display:flex;align-items:center;justify-content:center;margin:0 auto">
          <span style="color:white;font-size:24px">💰</span>
        </div>
        <h4>Savings</h4>
        <p>Best prices</p>
      </div>
    </div>

    <div class="main">
      <div class="main-left">
        <!-- Add Car Form for Admin -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
          <?php if (isset($_GET['action']) && $_GET['action'] == 'add_car'): ?>
            <div class="add-car-form">
              <h3>Add New Car</h3>
              <form method="POST">
                <div class="form-grid">
                  <div class="form-group">
                    <label for="name">Car Name</label>
                    <input type="text" id="name" name="name" required>
                  </div>
                  <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                      <option value="Luxury">Luxury</option>
                      <option value="Premium">Premium</option>
                      <option value="Sport">Sport</option>
                      <option value="Family">Family</option>
                      <option value="SUV">SUV</option>
                      <option value="Hatchback">Hatchback</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="price_per_day">Price per Day (IDR)</label>
                    <input type="number" id="price_per_day" name="price_per_day" required>
                  </div>
                  <div class="form-group">
                    <label for="seats">Seats</label>
                    <input type="number" id="seats" name="seats" required>
                  </div>
                  <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission" required>
                      <option value="Automatic">Automatic</option>
                      <option value="Manual">Manual</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="fuel_type">Fuel Type</label>
                    <input type="text" id="fuel_type" name="fuel_type" required>
                  </div>
                  <div class="form-group">
                    <label for="luggage">Luggage</label>
                    <input type="text" id="luggage" name="luggage" required>
                  </div>
                </div>
                <div class="form-actions">
                  <button type="button" onclick="window.location.href='Home2.php'" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Add Car</button>
                </div>
              </form>
            </div>
          <?php else: ?>
            <div style="text-align: right; margin-bottom: 15px;">
              <a href="Home2.php?action=add_car" class="btn-primary">
                <i class="fas fa-plus"></i> Add New Car
              </a>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <div class="featured-card">
          <!-- Featured Car Image -->
          <img src="Foto/Inova.jpg" alt="Luxury Car Rental">
          <div class="featured-list">
            <h3>Choose the car that suits you</h3>
            <ul>
              <li>Amazing performance for city & highway</li>
              <li>Flexible rental plans</li>
              <li>24/7 roadside assistance</li>
            </ul>
          </div>
        </div>

        <section id="catalog" class="catalogue">
          <div style="display:flex;justify-content:space-between;align-items:center;margin:12px 0">
            <h3 style="margin:0">Recommended Cars</h3>
            <a href="MenuPilihCar.php" style="color:var(--muted);text-decoration:none">View all &gt;</a>
          </div>

          <div class="card-grid">
            <!-- CAR IMAGES SECTION - Kelompokkan semua gambar mobil di sini -->
            <?php foreach ($cars as $car): ?>
            <div class="car-card">
              <!-- Car Image -->
              <div class="car-image">
                <img src="<?php echo $car['image_url']; ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
              </div>
              
              <!-- Car Details -->
              <h5><?php echo htmlspecialchars($car['name']); ?></h5>
              <p><?php echo htmlspecialchars($car['category']); ?>, <?php echo htmlspecialchars($car['seats'] ?? '5'); ?> seats</p>
              
              <!-- Price and Booking -->
              <div class="price-row">
                <strong>IDR <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?></strong>
                <a href="booking2.php?car_id=<?php echo $car['id']; ?>" class="btn-small">Booking</a>
              </div>

              <!-- CRUD Buttons for Admin -->
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
              <div class="crud-buttons">
                <form method="POST" style="display: inline;">
                  <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                  <button type="submit" name="toggle_availability" class="btn-toggle">
                    <i class="fas fa-sync-alt"></i> Toggle
                  </button>
                </form>
                <button class="btn-edit" onclick="editCar(<?php echo $car['id']; ?>)">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this car?')">
                  <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                  <button type="submit" name="delete_car" class="btn-delete">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </form>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>

        <div class="facts">
          <div class="fact"><h3>30k+</h3><p>Cars</p></div>
          <div class="fact"><h3>200k+</h3><p>Customers</p></div>
          <div class="fact"><h3>150+</h3><p>Locations</p></div>
          <div class="fact"><h3>4.9</h3><p>Avg Rating</p></div>
        </div>

        <div class="mini-cta">
          <div>
            <strong style="display:block">Enjoy every mile with adorable companionship.</strong>
            <span style="color:rgba(255,255,255,0.9)">Book now and get special deals</span>
          </div>
          <div><button>Get Started</button></div>
        </div>

      </div>

      <aside style="width:320px">
        <div style="background:white;border-radius:14px;padding:18px;box-shadow:0 8px 30px rgba(16,24,40,0.06)">
          <h4 style="margin:0 0 10px">Why choose us?</h4>
          <p style="color:var(--muted);margin:0">Best-in-class vehicles and 24/7 support.</p>
        </div>

        <div style="height:18px"></div>

        <div style="background:white;border-radius:14px;padding:18px;box-shadow:0 8px 30px rgba(16,24,40,0.06)">
          <h4 style="margin:0 0 10px">Need help?</h4>
          <p style="color:var(--muted);margin:0">Call +62 811 0000 000</p>
        </div>
      </aside>
    </div>

    <footer>
      <div>© 2025 Car Rental</div>
      <div class="social">Instagram • Facebook • Twitter</div>
    </footer>
  </div>

  <!-- Back to Top Button -->
  <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas">
    ↑
  </button>

  <script>
    // Back to Top Functionality
    const backToTopButton = document.getElementById('backToTop');

    // Tampilkan tombol ketika di-scroll ke bawah
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.add('show');
      } else {
        backToTopButton.classList.remove('show');
      }
    });

    // Scroll ke atas ketika tombol diklik
    backToTopButton.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    // Optional: Tambahkan keyboard support
    backToTopButton.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }
    });

    // Edit Car Function
    function editCar(carId) {
      // Redirect to edit page or show modal
      alert('Edit functionality for car ID: ' + carId + '\nThis would open an edit form or redirect to edit page.');
      // In real implementation: window.location.href = 'edit_car.php?id=' + carId;
    }

    // Confirm delete
    function confirmDelete(carId) {
      return confirm('Are you sure you want to delete car ID: ' + carId + '?');
    }
  </script>
</body>
</html>
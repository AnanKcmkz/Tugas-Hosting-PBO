<?php
session_start();

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Data mobil berdasarkan kategori
$cars = [
    'S-Class' => [
        'name' => 'S-Class',
        'type' => 'Luxury, 5 seats',
        'price' => 'IDR 1.500.000',
        'image' => 'images/s-class.jpg',
        'description' => 'The Mercedes-Benz S-Class is the epitome of luxury and innovation. With its elegant design, cutting-edge technology, and superior comfort, it is the perfect choice for business and leisure.',
        'features' => ['Premium Sound System', 'Leather Seats', 'Panoramic Sunroof', 'Advanced Safety Features']
    ],
    'Mercedes B-Class' => [
        'name' => 'Mercedes B-Class',
        'type' => 'Premium',
        'price' => 'IDR 1.200.000',
        'image' => 'images/B-class.jpg',
        'description' => 'The Mercedes B-Class offers a unique combination of compact dimensions, spacious interior, and premium features. It is ideal for city driving and family trips.',
        'features' => ['Spacious Interior', 'Fuel Efficient', 'Easy Parking', 'High Safety Standards']
    ],
    'BMW M5' => [
        'name' => 'BMW M5',
        'type' => 'Sport',
        'price' => 'IDR 950.000',
        'image' => 'images/m5.jpg',
        'description' => 'The BMW M5 is a high-performance sports sedan that delivers exhilarating driving dynamics without compromising on luxury and comfort.',
        'features' => ['Powerful Engine', 'Sporty Design', 'Luxurious Interior', 'Advanced Driving Modes']
    ],
    'Toyota Innova' => [
        'name' => 'Toyota Innova',
        'type' => 'Family',
        'price' => 'IDR 450.000',
        'image' => 'images/inova.png',
        'description' => 'The Toyota Innova is a reliable and spacious MPV perfect for family outings and long journeys. It offers comfort, durability, and great value.',
        'features' => ['7 Seats', 'Spacious Cabin', 'Reliable Performance', 'Family-Friendly']
    ],
    'Toyota Rush' => [
        'name' => 'Toyota Rush',
        'type' => 'SUV',
        'price' => 'IDR 480.000',
        'image' => 'images/rush.jpg',
        'description' => 'The Toyota Rush is a compact SUV that combines rugged style with urban practicality. It is designed for those who love adventure and city life.',
        'features' => ['Compact SUV', 'High Ground Clearance', 'Modern Design', 'Affordable']
    ],
    'Toyota Yaris' => [
        'name' => 'Toyota Yaris',
        'type' => 'Hatchback',
        'price' => 'IDR 350.000',
        'image' => 'images/yaris.png',
        'description' => 'The Toyota Yaris is a stylish and efficient hatchback that is perfect for city driving. It offers great fuel economy and a comfortable ride.',
        'features' => ['Compact Size', 'Fuel Efficient', 'Easy to Maneuver', 'Affordable']
    ]
];

// Ambil nama mobil dari parameter URL
$carName = isset($_GET['car']) ? $_GET['car'] : '';

// Jika mobil tidak ditemukan, redirect ke halaman utama
if (!array_key_exists($carName, $cars)) {
    header("Location: Home2.php");
    exit();
}

$car = $cars[$carName];
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Car Rental — <?php echo htmlspecialchars($car['name']); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --purple:#5b2df5;
      --purple-2:#6f46ff;
      --muted:#6b7280;
      --card:#ffffff;
      --bg:#f8f8ff;
      --accent:#ffb238;
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
    
    /* Detail Container */
    .detail-container {
      display: flex;
      gap: 40px;
      margin-top: 20px;
    }
    .car-image {
      flex: 1;
    }
    .car-image img {
      width: 100%;
      border-radius: var(--radius);
    }
    .car-details {
      flex: 1;
      background: white;
      padding: 24px;
      border-radius: var(--radius);
      box-shadow: 0 6px 20px rgba(16,24,40,0.06);
    }
    .car-details h1 {
      margin-top: 0;
    }
    .car-details .price {
      font-size: 24px;
      font-weight: bold;
      color: var(--purple);
    }
    .features {
      margin: 20px 0;
    }
    .features ul {
      padding-left: 20px;
    }
    .book-btn {
      padding: 12px 24px;
      background: var(--purple);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
      width: 100%;
      margin-top: 20px;
    }
    .book-btn:hover {
      background: var(--purple-2);
    }
    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: var(--purple);
      text-decoration: none;
      font-weight: 600;
    }
    .back-link:hover {
      text-decoration: underline;
    }

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
      box-shadow:0 2px 12px rgba(0,0,0,0.06)
    }
    nav ul {
      display:flex;
      gap:30px;
      list-style:none;
      margin:0;
      padding:0;
      font-weight:500
    }
    nav a {
      text-decoration:none;
      color:#111
    }

    /* Tombol yang disamakan */
    .cta, .book-btn, .btn-small, .mini-cta button {
      padding: 12px 24px;
      background: #5b2be7;
      color: #fff;
      border-radius: 8px;
      text-align: center;
      text-decoration: none;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    
    .cta:hover, .book-btn:hover, .btn-small:hover, .mini-cta button:hover {
      background: #4a1fd6;
    }

    footer{
      margin-top:30px;
      padding:24px 0;
      color:var(--muted);
      display:flex;
      justify-content:space-between;
      align-items:center
    }
    footer .social{display:flex;gap:8px}

    @media(max-width:768px){
      .detail-container {
        flex-direction: column;
      }
      .wrap{padding:14px;margin:18px}
    }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav>
    <div style="display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;color:#111">
      <span>🚗</span> <span>RentalCar</span>
    </div>

    <ul>
      <li><a href="Home2.php">Home</a></li>
      <li><a href="MenuPilihCar.php">All Cars</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>

    <div style="display:flex;align-items:center;gap:15px;">
      <span style="color:#5b2df5;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <a href="logout.php" style="padding:10px 18px;background:#5b2df5;color:white;border-radius:10px;font-weight:600;text-decoration:none">Logout</a>
    </div>
  </nav>

  <div class="wrap">
    <a href="Home2.php" class="back-link">&larr; Back to Home</a>
    
    <div class="detail-container">
      <div class="car-image">
        <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
      </div>
      <div class="car-details">
        <h1><?php echo htmlspecialchars($car['name']); ?></h1>
        <p><?php echo htmlspecialchars($car['type']); ?></p>
        <div class="price"><?php echo htmlspecialchars($car['price']); ?></div>
        <p><?php echo htmlspecialchars($car['description']); ?></p>
        <div class="features">
          <h3>Features:</h3>
          <ul>
            <?php foreach ($car['features'] as $feature): ?>
              <li><?php echo htmlspecialchars($feature); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <button class="book-btn">Book Now</button>
      </div>
    </div>

    <footer>
      <div>© 2025 Car Rental</div>
      <div class="social">Instagram • Facebook • Twitter</div>
    </footer>
  </div>
</body>
</html>
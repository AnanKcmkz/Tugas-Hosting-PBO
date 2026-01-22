<?php
session_start();

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Proses form booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulasi proses booking (dalam implementasi nyata, data akan disimpan ke database)
    $pickup_date = $_POST['pickup_date'];
    $dropoff_date = $_POST['dropoff_date'];
    $car_type = $_POST['car_type'];
    $location = $_POST['location'];
    $car_id = $_POST['car_id'];
    
    // Simpan data booking ke session untuk ditampilkan di halaman konfirmasi
    $_SESSION['booking_data'] = [
        'pickup_date' => $pickup_date,
        'dropoff_date' => $dropoff_date,
        'car_type' => $car_type,
        'location' => $location,
        'car_id' => $car_id
    ];
    
    // Redirect ke halaman konfirmasi
    header("Location: BookingConfirmation.php");
    exit();
}

// Data mobil yang tersedia (dalam implementasi nyata, ini akan diambil dari database)
$cars = [
    's-class' => [
        'name' => 'S-Class',
        'type' => 'Luxury',
        'seats' => '5 seats',
        'price' => 1500000,
        'image' => 'images/s-class.jpg'
    ],
    'b-class' => [
        'name' => 'Mercedes B-Class',
        'type' => 'Premium',
        'seats' => '5 seats',
        'price' => 1200000,
        'image' => 'images/B-class.jpg'
    ],
    'm5' => [
        'name' => 'BMW M5',
        'type' => 'Sport',
        'seats' => '5 seats',
        'price' => 950000,
        'image' => 'images/m5.jpg'
    ],
    'inova' => [
        'name' => 'Toyota Innova',
        'type' => 'Family',
        'seats' => '7 seats',
        'price' => 450000,
        'image' => 'images/inova.png'
    ],
    'rush' => [
        'name' => 'Toyota Rush',
        'type' => 'SUV',
        'seats' => '7 seats',
        'price' => 480000,
        'image' => 'images/rush.jpg'
    ],
    'yaris' => [
        'name' => 'Toyota Yaris',
        'type' => 'Hatchback',
        'seats' => '5 seats',
        'price' => 350000,
        'image' => 'images/yaris.png'
    ]
];

// Ambil data mobil berdasarkan ID jika ada parameter
$selected_car = null;
if (isset($_GET['car_id']) && array_key_exists($_GET['car_id'], $cars)) {
    $selected_car = $cars[$_GET['car_id']];
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book Your Car - RentalCar</title>
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

    /* Form Booking */
    .booking-container {
      display: flex;
      gap: 40px;
      margin-top: 20px;
    }
    
    .booking-form {
      flex: 2;
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(19,11,62,0.12);
    }
    
    .booking-summary {
      flex: 1;
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(19,11,62,0.12);
      align-self: flex-start;
    }
    
    .form-section {
      margin-bottom: 24px;
    }
    
    .form-section h3 {
      margin: 0 0 16px;
      font-size: 18px;
      color: #0f172a;
    }
    
    .field-group {
      display: flex;
      gap: 16px;
      margin-bottom: 16px;
    }
    
    .field {
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    
    .field label {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 6px;
      font-weight: 500;
    }
    
    .field input, .field select {
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #e6e7f2;
      font-family: inherit;
      font-size: 14px;
    }
    
    .field input:focus, .field select:focus {
      outline: none;
      border-color: var(--purple);
    }
    
    .car-selection {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 16px;
      margin-top: 16px;
    }
    
    .car-option {
      border: 2px solid #e6e7f2;
      border-radius: 12px;
      padding: 16px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .car-option:hover {
      border-color: var(--purple);
    }
    
    .car-option.selected {
      border-color: var(--purple);
      background-color: rgba(91, 45, 245, 0.05);
    }
    
    .car-option img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 8px;
    }
    
    .car-option h4 {
      margin: 0 0 4px;
      font-size: 16px;
    }
    
    .car-option p {
      margin: 0 0 8px;
      color: var(--muted);
      font-size: 14px;
    }
    
    .car-option .price {
      font-weight: 700;
      color: var(--purple);
    }
    
    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      padding-bottom: 12px;
      border-bottom: 1px solid #f1f1f1;
    }
    
    .summary-item.total {
      font-weight: 700;
      font-size: 18px;
      border-bottom: none;
      margin-top: 16px;
    }
    
    .selected-car {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }
    
    .selected-car img {
      width: 80px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }
    
    .selected-car-info h4 {
      margin: 0 0 4px;
    }
    
    .selected-car-info p {
      margin: 0;
      color: var(--muted);
      font-size: 14px;
    }
    
    .book-btn {
      width: 100%;
      padding: 16px;
      background: var(--purple);
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-top: 16px;
    }
    
    .book-btn:hover {
      background: var(--purple-2);
    }
    
    @media (max-width: 768px) {
      .booking-container {
        flex-direction: column;
      }
      
      .field-group {
        flex-direction: column;
      }
      
      .car-selection {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:18px 32px;position:sticky;top:0;z-index:50;background:white;box-shadow:0 2px 12px rgba(0,0,0,0.06)">
    <div style="display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;color:#111">
      <span>🚗</span> <span>RentalCar</span>
    </div>

    <ul style="display:flex;gap:30px;list-style:none;margin:0;padding:0;font-weight:500">
      <li><a href="Home2.php" style="text-decoration:none;color:#111">Home</a></li>
      <li><a href="MenuPilihCar.php" style="text-decoration:none;color:#111">All Cars</a></li>
      <li><a href="#contact" style="text-decoration:none;color:#111">Contact</a></li>
    </ul>

    <div style="display:flex;align-items:center;gap:15px;">
      <span style="color:#5b2df5;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <a href="logout.php" style="padding:10px 18px;background:#5b2df5;color:white;border-radius:10px;font-weight:600;text-decoration:none">Logout</a>
    </div>
  </nav>

  <div class="wrap">
    <h1 style="margin-bottom: 8px;">Book Your Car</h1>
    <p style="color: var(--muted); margin-bottom: 24px;">Complete the form below to reserve your vehicle</p>
    
    <div class="booking-container">
      <form class="booking-form" method="POST" action="">
        <!-- Informasi Pickup & Drop-off -->
        <div class="form-section">
          <h3>Pickup & Drop-off Information</h3>
          <div class="field-group">
            <div class="field">
              <label for="pickup_date">Pick-up Date</label>
              <input type="date" id="pickup_date" name="pickup_date" required>
            </div>
            <div class="field">
              <label for="dropoff_date">Drop-off Date</label>
              <input type="date" id="dropoff_date" name="dropoff_date" required>
            </div>
          </div>
          <div class="field">
            <label for="location">Pickup Location</label>
            <select id="location" name="location" required>
              <option value="">Select location</option>
              <option value="jakarta">Jakarta</option>
              <option value="bandung">Bandung</option>
              <option value="surabaya">Surabaya</option>
              <option value="bali">Bali</option>
              <option value="yogyakarta">Yogyakarta</option>
            </select>
          </div>
        </div>
        
        <!-- Pilih Mobil -->
        <div class="form-section">
          <h3>Select Your Car</h3>
          <div class="car-selection">
            <?php foreach ($cars as $id => $car): ?>
            <div class="car-option <?php echo ($selected_car && $selected_car['name'] === $car['name']) ? 'selected' : ''; ?>" 
                 onclick="selectCar('<?php echo $id; ?>', '<?php echo $car['name']; ?>', <?php echo $car['price']; ?>, '<?php echo $car['image']; ?>')">
              <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['name']; ?>">
              <h4><?php echo $car['name']; ?></h4>
              <p><?php echo $car['type']; ?> • <?php echo $car['seats']; ?></p>
              <div class="price">IDR <?php echo number_format($car['price'], 0, ',', '.'); ?>/day</div>
            </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" id="car_id" name="car_id" value="<?php echo $selected_car ? array_search($selected_car, $cars) : ''; ?>">
        </div>
        
        <!-- Informasi Tambahan -->
        <div class="form-section">
          <h3>Additional Information</h3>
          <div class="field">
            <label for="special_requests">Special Requests (Optional)</label>
            <textarea id="special_requests" name="special_requests" rows="4" placeholder="Any special requests or notes..."></textarea>
          </div>
        </div>
        
        <button type="submit" class="book-btn">Complete Booking</button>
      </form>
      
      <!-- Ringkasan Booking -->
      <div class="booking-summary">
        <h3 style="margin-top: 0;">Booking Summary</h3>
        
        <div id="selected-car-display" class="selected-car" style="<?php echo $selected_car ? '' : 'display:none;'; ?>">
          <?php if ($selected_car): ?>
          <img src="<?php echo $selected_car['image']; ?>" alt="<?php echo $selected_car['name']; ?>">
          <div class="selected-car-info">
            <h4><?php echo $selected_car['name']; ?></h4>
            <p><?php echo $selected_car['type']; ?> • <?php echo $selected_car['seats']; ?></p>
          </div>
          <?php endif; ?>
        </div>
        
        <div class="summary-item">
          <span>Car Rental</span>
          <span id="car-price"><?php echo $selected_car ? 'IDR ' . number_format($selected_car['price'], 0, ',', '.') : 'IDR 0'; ?></span>
        </div>
        <div class="summary-item">
          <span>Service Fee</span>
          <span>IDR 50.000</span>
        </div>
        <div class="summary-item">
          <span>Insurance</span>
          <span>IDR 100.000</span>
        </div>
        <div class="summary-item total">
          <span>Total</span>
          <span id="total-price"><?php echo $selected_car ? 'IDR ' . number_format($selected_car['price'] + 150000, 0, ',', '.') : 'IDR 0'; ?></span>
        </div>
        
        <div style="margin-top: 24px; padding: 16px; background: #f8f8ff; border-radius: 10px;">
          <h4 style="margin: 0 0 8px;">What's included:</h4>
          <ul style="margin: 0; padding-left: 16px; color: var(--muted);">
            <li>24/7 Roadside Assistance</li>
            <li>Free Cancellation up to 24 hours</li>
            <li>Comprehensive Insurance</li>
            <li>Unlimited Mileage</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Set minimum date untuk input tanggal (hari ini)
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('pickup_date').min = today;
    document.getElementById('dropoff_date').min = today;
    
    // Update min date untuk dropoff ketika pickup date dipilih
    document.getElementById('pickup_date').addEventListener('change', function() {
      document.getElementById('dropoff_date').min = this.value;
    });
    
    // Fungsi untuk memilih mobil
    function selectCar(carId, carName, carPrice, carImage) {
      // Update input hidden
      document.getElementById('car_id').value = carId;
      
      // Update tampilan mobil yang dipilih
      const selectedCarDisplay = document.getElementById('selected-car-display');
      selectedCarDisplay.innerHTML = `
        <img src="${carImage}" alt="${carName}">
        <div class="selected-car-info">
          <h4>${carName}</h4>
          <p>IDR ${carPrice.toLocaleString('id-ID')}/day</p>
        </div>
      `;
      selectedCarDisplay.style.display = 'flex';
      
      // Update harga
      document.getElementById('car-price').textContent = `IDR ${carPrice.toLocaleString('id-ID')}`;
      document.getElementById('total-price').textContent = `IDR ${(carPrice + 150000).toLocaleString('id-ID')}`;
      
      // Update tampilan pilihan mobil
      document.querySelectorAll('.car-option').forEach(option => {
        option.classList.remove('selected');
      });
      event.currentTarget.classList.add('selected');
    }
    
    // Jika ada mobil yang sudah dipilih dari parameter URL
    <?php if ($selected_car): ?>
    selectCar(
      '<?php echo array_search($selected_car, $cars); ?>',
      '<?php echo $selected_car['name']; ?>',
      <?php echo $selected_car['price']; ?>,
      '<?php echo $selected_car['image']; ?>'
    );
    <?php endif; ?>
  </script>
</body>
</html>
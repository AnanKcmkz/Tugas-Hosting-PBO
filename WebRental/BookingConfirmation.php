<?php
session_start();

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Cek apakah ada data booking
if (!isset($_SESSION['booking_data'])) {
    header("Location: MenuPilihCar.php");
    exit();
}

$booking_data = $_SESSION['booking_data'];

// Data mobil yang tersedia (sama dengan di halaman booking)
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

// Ambil data mobil yang dipesan
$selected_car = isset($cars[$booking_data['car_id']]) ? $cars[$booking_data['car_id']] : null;

if (!$selected_car) {
    header("Location: MenuPilihCar.php");
    exit();
}

// Generate nomor booking (dalam implementasi nyata, ini akan disimpan di database)
$booking_number = 'RC' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Hitung jumlah hari sewa
$pickup_date = new DateTime($booking_data['pickup_date']);
$dropoff_date = new DateTime($booking_data['dropoff_date']);
$days_rented = $pickup_date->diff($dropoff_date)->days;
if ($days_rented == 0) $days_rented = 1; // Minimal 1 hari

// Hitung total harga
$car_price_per_day = $selected_car['price'];
$service_fee = 50000;
$insurance = 100000;
$subtotal = $car_price_per_day * $days_rented;
$total_price = $subtotal + $service_fee + $insurance;

// Simpan data konfirmasi ke session (dalam implementasi nyata, akan disimpan ke database)
$_SESSION['confirmed_booking'] = [
    'booking_number' => $booking_number,
    'car' => $selected_car,
    'pickup_date' => $booking_data['pickup_date'],
    'dropoff_date' => $booking_data['dropoff_date'],
    'location' => $booking_data['location'],
    'days_rented' => $days_rented,
    'total_price' => $total_price,
    'booking_time' => date('Y-m-d H:i:s')
];
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking Confirmation - RentalCar</title>
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
      --success:#10b981;
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

    /* Konfirmasi Success */
    .confirmation-container {
      background: white;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 12px 30px rgba(19,11,62,0.12);
      text-align: center;
      margin-bottom: 24px;
    }
    
    .success-icon {
      width: 80px;
      height: 80px;
      background: var(--success);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    
    .success-icon svg {
      width: 40px;
      height: 40px;
      color: white;
    }
    
    .confirmation-container h1 {
      margin: 0 0 8px;
      color: var(--success);
    }
    
    .confirmation-container p {
      color: var(--muted);
      margin: 0 0 24px;
    }
    
    .booking-number {
      font-size: 24px;
      font-weight: 700;
      color: var(--purple);
      margin: 16px 0;
    }

    /* Detail Booking */
    .booking-details {
      display: flex;
      gap: 40px;
      margin-top: 20px;
    }
    
    .detail-card {
      flex: 2;
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(19,11,62,0.12);
    }
    
    .summary-card {
      flex: 1;
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(19,11,62,0.12);
      align-self: flex-start;
    }
    
    .section-title {
      margin: 0 0 16px;
      font-size: 18px;
      color: #0f172a;
      padding-bottom: 12px;
      border-bottom: 1px solid #f1f1f1;
    }
    
    .car-info {
      display: flex;
      gap: 16px;
      margin-bottom: 24px;
    }
    
    .car-info img {
      width: 200px;
      height: 140px;
      object-fit: cover;
      border-radius: 12px;
    }
    
    .car-details {
      flex: 1;
      text-align: left;
    }
    
    .car-details h3 {
      margin: 0 0 8px;
      font-size: 20px;
    }
    
    .car-details p {
      margin: 0 0 8px;
      color: var(--muted);
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-top: 16px;
    }
    
    .info-item {
      display: flex;
      flex-direction: column;
    }
    
    .info-label {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 4px;
    }
    
    .info-value {
      font-weight: 600;
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
    
    .action-buttons {
      display: flex;
      gap: 16px;
      margin-top: 32px;
      justify-content: center;
    }
    
    .btn-primary {
      padding: 12px 24px;
      background: var(--purple);
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-primary:hover {
      background: var(--purple-2);
    }
    
    .btn-secondary {
      padding: 12px 24px;
      background: white;
      color: var(--purple);
      border: 2px solid var(--purple);
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-secondary:hover {
      background: rgba(91, 45, 245, 0.05);
    }
    
    .next-steps {
      background: #f8f8ff;
      border-radius: 12px;
      padding: 20px;
      margin-top: 24px;
    }
    
    .next-steps h4 {
      margin: 0 0 12px;
    }
    
    .next-steps ul {
      margin: 0;
      padding-left: 16px;
      color: var(--muted);
    }
    
    .next-steps li {
      margin-bottom: 8px;
    }
    
    @media (max-width: 768px) {
      .booking-details {
        flex-direction: column;
      }
      
      .car-info {
        flex-direction: column;
      }
      
      .car-info img {
        width: 100%;
        height: 200px;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
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
    <!-- Konfirmasi Success -->
    <div class="confirmation-container">
      <div class="success-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h1>Booking Confirmed!</h1>
      <p>Your car rental has been successfully booked. Below are your booking details.</p>
      <div class="booking-number">Booking #<?php echo $booking_number; ?></div>
    </div>

    <div class="booking-details">
      <!-- Detail Booking -->
      <div class="detail-card">
        <h2 class="section-title">Booking Details</h2>
        
        <div class="car-info">
          <img src="<?php echo $selected_car['image']; ?>" alt="<?php echo $selected_car['name']; ?>">
          <div class="car-details">
            <h3><?php echo $selected_car['name']; ?></h3>
            <p><?php echo $selected_car['type']; ?> • <?php echo $selected_car['seats']; ?></p>
            <p>IDR <?php echo number_format($selected_car['price'], 0, ',', '.'); ?> / day</p>
          </div>
        </div>
        
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Pick-up Date</span>
            <span class="info-value"><?php echo date('l, F j, Y', strtotime($booking_data['pickup_date'])); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Drop-off Date</span>
            <span class="info-value"><?php echo date('l, F j, Y', strtotime($booking_data['dropoff_date'])); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Pick-up Location</span>
            <span class="info-value"><?php echo ucfirst($booking_data['location']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Rental Duration</span>
            <span class="info-value"><?php echo $days_rented; ?> day<?php echo $days_rented > 1 ? 's' : ''; ?></span>
          </div>
        </div>
        
        <div class="next-steps">
          <h4>What's Next?</h4>
          <ul>
            <li>You will receive a confirmation email shortly</li>
            <li>Bring your ID and driver's license when picking up the vehicle</li>
            <li>Our team will contact you 24 hours before pickup for final confirmation</li>
            <li>Contact us at +62 811 0000 000 if you have any questions</li>
          </ul>
        </div>
      </div>
      
      <!-- Ringkasan Pembayaran -->
      <div class="summary-card">
        <h2 class="section-title">Payment Summary</h2>
        
        <div class="summary-item">
          <span>Car Rental (<?php echo $days_rented; ?> day<?php echo $days_rented > 1 ? 's' : ''; ?>)</span>
          <span>IDR <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
        </div>
        <div class="summary-item">
          <span>Service Fee</span>
          <span>IDR <?php echo number_format($service_fee, 0, ',', '.'); ?></span>
        </div>
        <div class="summary-item">
          <span>Insurance</span>
          <span>IDR <?php echo number_format($insurance, 0, ',', '.'); ?></span>
        </div>
        <div class="summary-item total">
          <span>Total</span>
          <span>IDR <?php echo number_format($total_price, 0, ',', '.'); ?></span>
        </div>
        
        <div style="margin-top: 24px; padding: 16px; background: #f0f7ff; border-radius: 10px;">
          <h4 style="margin: 0 0 8px; color: #1e40af;">Payment Status: <span style="color: var(--success);">Paid</span></h4>
          <p style="margin: 0; font-size: 14px; color: var(--muted);">Payment processed successfully</p>
        </div>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
      <a href="Home2.php" class="btn-primary">Back to Home</a>
      <a href="MenuPilihCar.php" class="btn-secondary">Book Another Car</a>
      <button class="btn-secondary" onclick="window.print()">Print Confirmation</button>
    </div>
  </div>

  <script>
    // Tambahkan efek animasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
      const confirmationContainer = document.querySelector('.confirmation-container');
      confirmationContainer.style.opacity = '0';
      confirmationContainer.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        confirmationContainer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        confirmationContainer.style.opacity = '1';
        confirmationContainer.style.transform = 'translateY(0)';
      }, 100);
    });
  </script>
</body>
</html>
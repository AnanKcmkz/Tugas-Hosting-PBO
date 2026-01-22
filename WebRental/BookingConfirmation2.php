<?php
session_start();
require_once 'config.php';

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Ambil data booking dari parameter URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id > 0) {
    // Ambil data booking beserta data mobil dan user - hanya yang sesuai dengan user yang login
    // DIPERBAIKI: Hanya ambil kolom yang ada di database
    $stmt = $pdo->prepare("
        SELECT b.*, c.name as car_name, c.category as car_category, c.image as car_image, 
               c.price_per_day, c.transmission, c.fuel_type, c.seats, c.luggage,
               u.username, u.email
        FROM bookings b 
        JOIN cars c ON b.car_id = c.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $_SESSION['error'] = "Booking tidak ditemukan atau Anda tidak memiliki akses";
        header("Location: Home2.php");
        exit();
    }
    
    // Set default values untuk field yang mungkin kosong
    $booking['transmission'] = $booking['transmission'] ?? 'Automatic';
    $booking['fuel_type'] = $booking['fuel_type'] ?? 'Premium';
    $booking['seats'] = $booking['seats'] ?? '5';
    $booking['luggage'] = $booking['luggage'] ?? '2 bags';
    
} else {
    $_SESSION['error'] = "ID Booking tidak valid";
    header("Location: Home2.php");
    exit();
}

// Hitung ulang total days untuk memastikan konsistensi
$pickup = new DateTime($booking['pickup_date']);
$return = new DateTime($booking['return_date']);
$interval = $pickup->diff($return);
$total_days = $interval->days + 1; // Include both start and end date

// Validasi jika total days tidak sesuai
if ($total_days != $booking['total_days']) {
    // Update total days dan total price di database
    $new_total_price = $total_days * $booking['price_per_day'];
    $update_stmt = $pdo->prepare("UPDATE bookings SET total_days = ?, total_price = ? WHERE id = ?");
    $update_stmt->execute([$total_days, $new_total_price, $booking_id]);
    $booking['total_days'] = $total_days;
    $booking['total_price'] = $new_total_price;
}

// Format tanggal untuk tampilan
$pickup_date_formatted = date('F j, Y', strtotime($booking['pickup_date']));
$return_date_formatted = date('F j, Y', strtotime($booking['return_date']));
$booking_date_formatted = date('F j, Y \a\t H:i', strtotime($booking['created_at'] ?? 'now'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking Confirmation - Car Rental</title>
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
      line-height:1.6;
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
      text-decoration:none;
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
      transition:color 0.3s;
    }
    
    nav a:hover {
      color:var(--purple);
    }
    
    .user-info {
      display:flex;
      align-items:center;
      gap:15px;
    }
    
    .user-info span {
      color:#5b2df5;
      font-weight:500;
    }
    
    .logout-btn {
      padding:10px 18px;
      background:#5b2df5;
      color:white;
      border-radius:10px;
      font-weight:600;
      text-decoration:none;
      transition:background 0.3s;
    }
    
    .logout-btn:hover {
      background:#4a1fd6;
    }
    
    /* Success Section */
    .success-container {
      text-align: center;
      max-width: 700px;
      margin: 40px auto;
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      position:relative;
      overflow:hidden;
    }
    
    .success-container::before {
      content:'';
      position:absolute;
      top:0;
      left:0;
      right:0;
      height:4px;
      background:linear-gradient(90deg, var(--success), var(--purple));
    }
    
    .success-icon {
      width: 80px;
      height: 80px;
      background: var(--success);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow:0 5px 15px rgba(16,185,129,0.3);
    }
    
    .success-icon span {
      color: white;
      font-size: 40px;
    }
    
    .booking-id {
      background: var(--purple);
      color: white;
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 20px;
      box-shadow:0 3px 10px rgba(91,45,245,0.2);
    }
    
    .success-title {
      font-size: 32px;
      margin: 0 0 10px;
      color: #111;
      font-weight:700;
    }
    
    .success-message {
      color: var(--muted);
      margin: 0 0 30px;
      font-size: 16px;
      max-width:500px;
      margin-left:auto;
      margin-right:auto;
    }
    
    .car-info {
      display: flex;
      gap: 20px;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      background: white;
      border-radius: 12px;
      border: 1px solid #e6e7f2;
      text-align:left;
      transition:transform 0.3s, box-shadow 0.3s;
    }
    
    .car-info:hover {
      transform:translateY(-2px);
      box-shadow:0 5px 15px rgba(0,0,0,0.1);
    }
    
    .car-image {
      width: 120px;
      height: 80px;
      border-radius: 8px;
      object-fit: cover;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      flex-shrink:0;
      overflow:hidden;
    }
    
    .car-image img {
      width:100%;
      height:100%;
      object-fit:cover;
    }
    
    .car-details {
      flex: 1;
    }
    
    .car-name {
      font-size: 18px;
      margin: 0 0 5px;
      color: #111;
      font-weight:600;
    }
    
    .car-category {
      color: var(--muted);
      margin: 0 0 8px;
    }
    
    .car-specs {
      display:flex;
      gap:15px;
      flex-wrap:wrap;
    }
    
    .car-spec {
      display:flex;
      align-items:center;
      gap:5px;
      font-size:13px;
      color:var(--muted);
    }
    
    .booking-details {
      background: var(--bg);
      border-radius: 15px;
      padding: 25px;
      margin: 30px 0;
      text-align: left;
      border:1px solid #e6e7f2;
    }
    
    .detail-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #e6e7f2;
    }
    
    .detail-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .detail-label {
      color: var(--muted);
      font-weight: 500;
      display:flex;
      align-items:center;
      gap:8px;
    }
    
    .detail-value {
      font-weight: 600;
      text-align: right;
    }
    
    .action-buttons {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
      flex-wrap:wrap;
    }
    
    .btn-primary {
      padding: 14px 28px;
      background: var(--purple);
      color: white;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      display:flex;
      align-items:center;
      gap:8px;
      border:none;
      cursor:pointer;
      font-size:15px;
    }
    
    .btn-primary:hover {
      background: var(--purple-2);
      transform:translateY(-2px);
      box-shadow:0 5px 15px rgba(91,45,245,0.3);
    }
    
    .btn-secondary {
      padding: 14px 28px;
      background: white;
      color: var(--purple);
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      border: 1px solid var(--purple);
      transition: all 0.3s;
      display:flex;
      align-items:center;
      gap:8px;
    }
    
    .btn-secondary:hover {
      background: var(--bg);
      transform:translateY(-2px);
    }
    
    .next-steps {
      background: #f0f9ff;
      border-radius: 12px;
      padding: 20px;
      margin-top: 30px;
      text-align: left;
      border-left:4px solid var(--purple);
    }
    
    .next-steps h3 {
      margin: 0 0 15px;
      color: #111;
      font-size: 18px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    
    .next-steps ul {
      margin: 0;
      padding-left: 20px;
      color: var(--muted);
    }
    
    .next-steps li {
      margin-bottom: 8px;
      line-height:1.5;
    }
    
    .status-badge {
      display:inline-block;
      padding:5px 12px;
      border-radius:20px;
      font-size:12px;
      font-weight:600;
      margin-left:10px;
    }
    
    .status-pending {
      background:#fef3c7;
      color:#d97706;
    }
    
    .status-confirmed {
      background:#d1fae5;
      color:#059669;
    }
    
    .print-btn {
      position:absolute;
      top:20px;
      right:20px;
      background:white;
      border:1px solid #e6e7f2;
      border-radius:8px;
      padding:8px 12px;
      cursor:pointer;
      color:var(--muted);
      transition:all 0.3s;
    }
    
    .print-btn:hover {
      background:var(--bg);
      color:var(--purple);
    }
    
    @media(max-width: 768px) {
      .success-container {
        padding: 25px;
        margin: 20px auto;
      }
      
      .car-info {
        flex-direction: column;
        text-align: center;
      }
      
      .car-specs {
        justify-content:center;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      nav {
        flex-direction: column;
        gap: 15px;
        padding: 15px 20px;
      }
      
      nav ul {
        gap: 15px;
      }
      
      .detail-row {
        flex-direction: column;
        gap: 5px;
      }
      
      .detail-value {
        text-align: left;
      }
      
      .print-btn {
        position:static;
        margin-bottom:15px;
        align-self:flex-end;
      }
    }
    
    @media(max-width: 480px) {
      .success-container {
        padding: 15px;
        margin: 10px auto;
      }
      
      .booking-details {
        padding: 15px;
      }
      
      .success-title {
        font-size:24px;
      }
    }
    
    /* Print Styles */
    @media print {
      nav, .action-buttons, .print-btn {
        display: none !important;
      }
      
      body {
        background: white;
      }
      
      .success-container {
        box-shadow: none;
        margin: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav>
    <a href="Home2.php" class="logo">
      <span>🚗</span> <span>RentalCar</span>
    </a>

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
    <div class="success-container">
      <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print
      </button>
      
      <div class="success-icon">
        <span>✓</span>
      </div>
      
      <div class="booking-id">Booking #<?php echo htmlspecialchars($booking['id']); ?></div>
      
      <h1 class="success-title">Booking Confirmed!</h1>
      <p class="success-message">Thank you for your booking. Your rental car has been reserved successfully.</p>
      
      <div class="car-info">
        <div class="car-image">
          <?php if (!empty($booking['car_image']) && file_exists('images/' . $booking['car_image'])): ?>
            <img src="images/<?php echo htmlspecialchars($booking['car_image']); ?>" 
                 alt="<?php echo htmlspecialchars($booking['car_name']); ?>">
          <?php else: ?>
            <i class="fas fa-car" style="font-size:24px;"></i>
          <?php endif; ?>
        </div>
        <div class="car-details">
          <h3 class="car-name"><?php echo htmlspecialchars($booking['car_name']); ?></h3>
          <p class="car-category"><?php echo htmlspecialchars($booking['car_category']); ?></p>
          <div class="car-specs">
            <div class="car-spec">
              <i class="fas fa-cog"></i>
              <span><?php echo htmlspecialchars($booking['transmission']); ?></span>
            </div>
            <div class="car-spec">
              <i class="fas fa-gas-pump"></i>
              <span><?php echo htmlspecialchars($booking['fuel_type']); ?></span>
            </div>
            <div class="car-spec">
              <i class="fas fa-user"></i>
              <span><?php echo htmlspecialchars($booking['seats']); ?> seats</span>
            </div>
            <div class="car-spec">
              <i class="fas fa-suitcase"></i>
              <span><?php echo htmlspecialchars($booking['luggage']); ?></span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="booking-details">
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-user"></i>
            Customer Name
          </span>
          <!-- DIPERBAIKI: Gunakan username karena full_name tidak ada -->
          <span class="detail-value"><?php echo htmlspecialchars($booking['username']); ?></span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-envelope"></i>
            Email
          </span>
          <span class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-calendar-check"></i>
            Booking Date
          </span>
          <span class="detail-value"><?php echo $booking_date_formatted; ?></span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-map-marker-alt"></i>
            Pick-up Date & Location
          </span>
          <span class="detail-value">
            <strong><?php echo $pickup_date_formatted; ?></strong><br>
            <?php echo htmlspecialchars($booking['pickup_location']); ?>
          </span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-map-marker-alt"></i>
            Return Date & Location
          </span>
          <span class="detail-value">
            <strong><?php echo $return_date_formatted; ?></strong><br>
            <?php echo htmlspecialchars($booking['return_location']); ?>
          </span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-clock"></i>
            Rental Duration
          </span>
          <span class="detail-value"><?php echo htmlspecialchars($booking['total_days']); ?> days</span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">
            <i class="fas fa-tag"></i>
            Daily Rate
          </span>
          <span class="detail-value">IDR <?php echo number_format($booking['price_per_day'], 0, ',', '.'); ?></span>
        </div>
        
        <div class="detail-row" style="border-top: 2px solid #e6e7f2; padding-top: 15px; font-size: 18px;">
          <span class="detail-label">
            <i class="fas fa-receipt"></i>
            Total Amount
          </span>
          <span class="detail-value" style="color: var(--purple); font-size:20px;">
            IDR <?php echo number_format($booking['total_price'], 0, ',', '.'); ?>
          </span>
        </div>
      </div>
      
      <div class="next-steps">
        <h3>
          <i class="fas fa-info-circle"></i>
          What's Next?
        </h3>
        <ul>
          <li><strong>Confirmation:</strong> Your booking has been confirmed</li>
          <li><strong>Pick-up Requirements:</strong> Bring your driver's license and credit card when picking up the vehicle</li>
          <li><strong>Modifications:</strong> Contact us at least 24 hours in advance for any changes to your booking</li>
          <li><strong>Cancellation:</strong> Free cancellation up to 24 hours before pick-up time</li>
        </ul>
      </div>
      
      <div class="action-buttons">
        <a href="Home2.php" class="btn-primary">
          <i class="fas fa-home"></i>
          Back to Home
        </a>
        <a href="MenuPilihCar.php" class="btn-secondary">
          <i class="fas fa-car"></i>
          Book Another Car
        </a>
        <!-- DIPERBAIKI: Hapus link ke my_bookings.php jika file belum ada -->
        <a href="Home2.php" class="btn-secondary">
          <i class="fas fa-list"></i>
          My Bookings
        </a>
      </div>
    </div>
  </div>

  <script>
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
      // Add animation to success icon
      const successIcon = document.querySelector('.success-icon');
      successIcon.style.transform = 'scale(0)';
      setTimeout(() => {
        successIcon.style.transition = 'transform 0.5s ease-out';
        successIcon.style.transform = 'scale(1)';
      }, 100);
      
      // Print functionality
      const printBtn = document.querySelector('.print-btn');
      printBtn.addEventListener('click', function() {
        window.print();
      });
    });
  </script>
</body>
</html>
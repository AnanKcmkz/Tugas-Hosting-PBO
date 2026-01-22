<?php
session_start();
require_once 'config.php';

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Ambil data mobil dari parameter URL
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;

if ($car_id > 0) {
    // Ambil data mobil dari database dengan field yang lengkap
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        // Jika mobil tidak ditemukan, redirect ke home
        header("Location: Home2.php");
        exit();
    }
    
    // Set nilai default untuk field yang mungkin tidak ada
    $car['transmission'] = $car['transmission'] ?? 'Automatic';
    $car['fuel_type'] = $car['fuel_type'] ?? 'Premium';
    $car['luggage'] = $car['luggage'] ?? '2 bags';
    $car['seats'] = $car['seats'] ?? '5';
    $car['features'] = $car['features'] ?? 'Air Conditioning, Bluetooth, GPS Navigation';
    
} else {
    header("Location: Home2.php");
    exit();
}

// Proses form booking jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_location = $_POST['pickup_location'];
    $return_location = $_POST['return_location'];
    $total_days = $_POST['total_days'];
    $total_price = $_POST['total_price'];
    
    // Validasi tanggal
    if (strtotime($return_date) <= strtotime($pickup_date)) {
        $error = "Return date must be after pickup date";
    } else {
        // Simpan booking ke database
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, pickup_date, return_date, pickup_location, return_location, total_days, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $car_id, $pickup_date, $return_date, $pickup_location, $return_location, $total_days, $total_price]);
        
        // Redirect ke halaman konfirmasi
        header("Location: bookingConfirmation2.php?booking_id=" . $pdo->lastInsertId());
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book <?php echo htmlspecialchars($car['name']); ?> - Car Rental</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --purple:#5b2df5;
      --purple-2:#6f46ff;
      --muted:#6b7280;
      --card:#ffffff;
      --bg:#f8f8ff;
      --accent:#ffb238;
      --error:#ef4444;
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
    
    /* Booking Section */
    .booking-container {
      display: flex;
      gap: 40px;
      margin-top: 30px;
    }
    
    .car-details {
      flex: 1;
      background: white;
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 8px 30px rgba(16,24,40,0.06);
    }
    
    .car-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 20px;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      overflow: hidden;
    }
    
    .car-name {
      font-size: 24px;
      margin: 0 0 10px;
      color: #111;
    }
    
    .car-category {
      color: var(--muted);
      margin: 0 0 15px;
      font-size: 16px;
    }
    
    .car-features {
      margin: 20px 0;
    }
    
    .feature-item {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      font-size: 15px;
    }
    
    .feature-item i {
      margin-right: 10px;
      color: var(--purple);
      width: 20px;
      text-align: center;
    }
    
    .feature-list {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid #e6e7f2;
    }
    
    .feature-list h4 {
      margin: 0 0 10px;
      color: #111;
    }
    
    .feature-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    
    .feature-tag {
      background: var(--bg);
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      color: var(--muted);
    }
    
    .booking-form {
      flex: 1;
      background: white;
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 8px 30px rgba(16,24,40,0.06);
    }
    
    .form-title {
      font-size: 20px;
      margin: 0 0 20px;
      color: #111;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    
    .form-group input, .form-group select {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #e6e7f2;
      font-family: inherit;
      transition: border-color 0.3s;
    }
    
    .form-group input:focus, .form-group select:focus {
      outline: none;
      border-color: var(--purple);
    }
    
    .date-row {
      display: flex;
      gap: 15px;
    }
    
    .date-row .form-group {
      flex: 1;
    }
    
    .price-summary {
      background: var(--bg);
      border-radius: 10px;
      padding: 20px;
      margin: 20px 0;
    }
    
    .price-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    
    .price-total {
      border-top: 1px solid #e6e7f2;
      padding-top: 10px;
      margin-top: 10px;
      font-weight: 700;
      font-size: 18px;
    }
    
    .book-btn {
      width: 100%;
      padding: 14px;
      background: var(--purple);
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .book-btn:hover {
      background: var(--purple-2);
    }
    
    .book-btn:disabled {
      background: var(--muted);
      cursor: not-allowed;
    }
    
    .back-link {
      display: inline-block;
      margin-top: 20px;
      color: var(--purple);
      text-decoration: none;
      font-weight: 500;
    }
    
    .error-message {
      background: #fef2f2;
      color: var(--error);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #fecaca;
    }
    
    .info-message {
      background: #f0f9ff;
      color: var(--purple);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #bae6fd;
    }
    
    .section-title {
      font-size: 24px;
      margin: 0 0 20px;
      color: #111;
    }
    
    @media(max-width: 768px) {
      .booking-container {
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
      
      .date-row {
        flex-direction: column;
        gap: 10px;
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
    <h1 class="section-title">Book Your Car</h1>
    
    <?php if (isset($error)): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <div class="booking-container">
      <div class="car-details">
        <!-- Gambar mobil -->
        <div class="car-image">
          <?php if (!empty($car['image']) && file_exists('images/' . $car['image'])): ?>
            <img src="images/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:12px">
          <?php else: ?>
            <span>Image of <?php echo htmlspecialchars($car['name']); ?></span>
          <?php endif; ?>
        </div>
        
        <h2 class="car-name"><?php echo htmlspecialchars($car['name']); ?></h2>
        <p class="car-category"><?php echo htmlspecialchars($car['category']); ?> • <?php echo htmlspecialchars($car['seats']); ?> Seats</p>
        
        <div class="car-features">
          <div class="feature-item">
            <i>⚙️</i> <span><?php echo htmlspecialchars($car['transmission']); ?> Transmission</span>
          </div>
          <div class="feature-item">
            <i>⛽</i> <span><?php echo htmlspecialchars($car['fuel_type']); ?></span>
          </div>
          <div class="feature-item">
            <i>📏</i> <span><?php echo htmlspecialchars($car['luggage']); ?> Luggage Capacity</span>
          </div>
        </div>
        
        <div class="feature-list">
          <h4>Car Features</h4>
          <div class="feature-tags">
            <?php
            $features = explode(',', $car['features']);
            foreach ($features as $feature):
              $feature = trim($feature);
              if (!empty($feature)):
            ?>
              <span class="feature-tag"><?php echo htmlspecialchars($feature); ?></span>
            <?php 
              endif;
            endforeach; 
            ?>
          </div>
        </div>
        
        <div class="price-summary">
          <div class="price-row">
            <span>Daily Rate:</span>
            <span>IDR <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?></span>
          </div>
        </div>
      </div>
      
      <div class="booking-form">
        <h3 class="form-title">Booking Details</h3>
        
        <div class="info-message">
          Please fill in your booking details below. All fields are required.
        </div>
        
        <form method="POST" id="bookingForm">
          <div class="date-row">
            <div class="form-group">
              <label for="pickup_date">Pick-up Date</label>
              <input type="date" id="pickup_date" name="pickup_date" required>
            </div>
            <div class="form-group">
              <label for="return_date">Return Date</label>
              <input type="date" id="return_date" name="return_date" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="pickup_location">Pick-up Location</label>
            <select id="pickup_location" name="pickup_location" required>
              <option value="">Select location</option>
              <option value="Jakarta">Jakarta</option>
              <option value="Bandung">Bandung</option>
              <option value="Surabaya">Surabaya</option>
              <option value="Bali">Bali</option>
              <option value="Yogyakarta">Yogyakarta</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="return_location">Return Location</label>
            <select id="return_location" name="return_location" required>
              <option value="">Select location</option>
              <option value="Jakarta">Jakarta</option>
              <option value="Bandung">Bandung</option>
              <option value="Surabaya">Surabaya</option>
              <option value="Bali">Bali</option>
              <option value="Yogyakarta">Yogyakarta</option>
            </select>
          </div>
          
          <div class="price-summary">
            <div class="price-row">
              <span>Daily Rate:</span>
              <span>IDR <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?></span>
            </div>
            <div class="price-row">
              <span>Rental Days:</span>
              <span id="days_display">0 days</span>
            </div>
            <div class="price-row price-total">
              <span>Total Price:</span>
              <span id="total_price">IDR 0</span>
            </div>
          </div>
          
          <input type="hidden" id="total_days" name="total_days" value="0">
          <input type="hidden" id="total_price_value" name="total_price" value="0">
          
          <button type="submit" class="book-btn" id="submitBtn">Complete Booking</button>
        </form>
        
        <a href="Home2.php" class="back-link">← Back to Home</a>
      </div>
    </div>
  </div>

  <script>
    // Calculate rental days and total price
    function calculatePrice() {
      const pickupDate = new Date(document.getElementById('pickup_date').value);
      const returnDate = new Date(document.getElementById('return_date').value);
      const pricePerDay = <?php echo $car['price_per_day']; ?>;
      const submitBtn = document.getElementById('submitBtn');
      
      if (pickupDate && returnDate && returnDate > pickupDate) {
        const timeDiff = returnDate.getTime() - pickupDate.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        document.getElementById('days_display').textContent = daysDiff + ' days';
        document.getElementById('total_days').value = daysDiff;
        
        const totalPrice = daysDiff * pricePerDay;
        document.getElementById('total_price').textContent = 'IDR ' + totalPrice.toLocaleString('id-ID');
        document.getElementById('total_price_value').value = totalPrice;
        
        // Enable submit button
        submitBtn.disabled = false;
      } else {
        document.getElementById('days_display').textContent = '0 days';
        document.getElementById('total_days').value = '0';
        document.getElementById('total_price').textContent = 'IDR 0';
        document.getElementById('total_price_value').value = '0';
        
        // Disable submit button
        submitBtn.disabled = true;
      }
    }
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('pickup_date').min = today;
    document.getElementById('return_date').min = today;
    
    // Add event listeners
    document.getElementById('pickup_date').addEventListener('change', function() {
      const pickupDate = this.value;
      document.getElementById('return_date').min = pickupDate;
      
      // If return date is before new pickup date, clear it
      const returnDate = document.getElementById('return_date').value;
      if (returnDate && returnDate < pickupDate) {
        document.getElementById('return_date').value = '';
      }
      
      calculatePrice();
    });
    
    document.getElementById('return_date').addEventListener('change', calculatePrice);
    
    // Initially disable submit button
    document.getElementById('submitBtn').disabled = true;
    
    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
      const pickupDate = document.getElementById('pickup_date').value;
      const returnDate = document.getElementById('return_date').value;
      const pickupLocation = document.getElementById('pickup_location').value;
      const returnLocation = document.getElementById('return_location').value;
      
      if (!pickupDate || !returnDate || !pickupLocation || !returnLocation) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
      }
      
      if (new Date(returnDate) <= new Date(pickupDate)) {
        e.preventDefault();
        alert('Return date must be after pickup date.');
        return false;
      }
    });
  </script>
</body>
</html>
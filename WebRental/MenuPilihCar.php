<?php
session_start();
require_once 'config.php';

// Validasi session - hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Ambil data mobil dari database
$category = isset($_GET['category']) ? $_GET['category'] : '';
if ($category && $category != 'all') {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE category = ? AND available = 1");
    $stmt->execute([$category]);
} else {
    $stmt = $pdo->query("SELECT * FROM cars WHERE available = 1");
}
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Cars - Car Rental</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter', Arial, sans-serif;}
body{background:#f8f8ff;color:#222;}

/* NAVBAR */
.navbar{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,0.1);} 
.navbar ul{display:flex;list-style:none;gap:30px;} 
.navbar a{text-decoration:none;color:#222;font-weight:500;transition: color 0.3s;} 
.navbar a:hover{color:#5a3df0;}
.contact{font-weight:bold;} 

/* TITLE */
.title{text-align:center;margin:40px 0 20px;font-size:30px;font-weight:bold;}

/* FILTER BUTTONS */
.filters{text-align:center;margin-bottom:30px;} 
.filters a{text-decoration:none;}
.filters button{padding:10px 20px;border:none;border-radius:25px;background:#ececec;margin:5px;cursor:pointer;transition: all 0.3s;} 
.filters button.active{background:#5a3df0;color:#fff;} 
.filters button:hover{background:#5a3df0;color:#fff;}

/* CAR GRID */
.cars{width:90%;margin:auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:30px;} 
.car-card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);transition: transform 0.3s, box-shadow 0.3s;position:relative;overflow:hidden;} 
.car-card:hover{transform:translateY(-8px);box-shadow:0 8px 25px rgba(0,0,0,0.15);}
.car-image{width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:15px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#999;font-size:14px;} 
.car-name{font-size:20px;font-weight:bold;margin-top:10px;color:#222;} 
.car-price{color:#5a3df0;font-weight:bold;margin:8px 0;font-size:18px;} 
.car-info{font-size:14px;color:#666;margin-bottom:15px;display:flex;align-items:center;gap:8px;} 
.car-info i{color:#5a3df0;}
.view-btn{width:100%;padding:12px;background:#5a3df0;color:#fff;border:none;border-radius:8px;font-size:15px;margin-top:10px;cursor:pointer;transition: background 0.3s;font-weight:600;text-decoration:none;display:block;text-align:center;} 
.view-btn:hover{background:#4a1fd6;}

/* Category Badge */
.category-badge{position:absolute;top:15px;right:15px;background:#5a3df0;color:white;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;}

/* BRAND LOGOS */
.brands{width:90%;margin:50px auto;text-align:center;display:flex;justify-content:space-around;flex-wrap:wrap;gap:30px;} 
.brand-logo{height:40px;opacity:0.7;transition: opacity 0.3s;background:#f0f0f0;padding:8px;border-radius:5px;} 
.brand-logo:hover{opacity:1;}

/* FOOTER */
.footer{background:#fff;margin-top:40px;padding:40px 0;} 
.footer-content{width:90%;margin:auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:30px;} 
.footer-title{font-weight:bold;margin-bottom:15px;font-size:18px;} 
.footer p{font-size:14px;color:#555;margin-bottom:8px;} 
.copy{text-align:center;font-size:13px;margin-top:20px;color:#777;} 

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.user-info span {
    color: #5b2df5;
    font-weight: 500;
}
.logout-btn {
    padding: 8px 16px;
    background: #5b2df5;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
}
.logout-btn:hover {
    background: #4a1fd6;
}

/* Responsive */
@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        padding: 15px;
        gap: 15px;
    }
    .navbar ul {
        gap: 15px;
    }
    .cars {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .filters {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    .brands {
        gap: 20px;
    }
    .brand-logo {
        height: 30px;
    }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
    grid-column: 1 / -1;
}
.empty-state i {
    font-size: 50px;
    margin-bottom: 15px;
    color: #ccc;
}

</style>
</head>
<body>

<header class="navbar">
    <div class="logo" style="display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;color:#111">
        <span>🚗</span> <span>RentalCar</span>
    </div>
    <ul>
        <li><a href="Home2.php">Home</a></li>
        <li><a href="MenuPilihCar.php" style="color:#5a3df0;">All Cars</a></li>
        <li><a href="ContactUs.php">Contact Us</a></li>
    </ul>
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<h2 class="title">Our Car Collection</h2>

<div class="filters">
    <a href="?category=all"><button class="<?php echo (!$category || $category == 'all') ? 'active' : ''; ?>">All Vehicles</button></a>
    <a href="?category=Luxury"><button class="<?php echo $category == 'Luxury' ? 'active' : ''; ?>">Luxury</button></a>
    <a href="?category=Premium"><button class="<?php echo $category == 'Premium' ? 'active' : ''; ?>">Premium</button></a>
    <a href="?category=Sport"><button class="<?php echo $category == 'Sport' ? 'active' : ''; ?>">Sport</button></a>
    <a href="?category=SUV"><button class="<?php echo $category == 'SUV' ? 'active' : ''; ?>">SUV</button></a>
    <a href="?category=Family"><button class="<?php echo $category == 'Family' ? 'active' : ''; ?>">Family</button></a>
    <a href="?category=Hatchback"><button class="<?php echo $category == 'Hatchback' ? 'active' : ''; ?>">Hatchback</button></a>
</div>

<!-- CAR GRID -->
<div class="cars">
    <?php if (count($cars) > 0): ?>
        <?php foreach($cars as $car): ?>
        <div class="car-card">
            <div class="category-badge"><?php echo htmlspecialchars($car['category']); ?></div>
            <div class="car-image">
                <?php if (!empty($car['image']) && file_exists('images/' . $car['image'])): ?>
                    <img src="images/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
                <?php else: ?>
                    <span><?php echo htmlspecialchars($car['name']); ?></span>
                <?php endif; ?>
            </div>
            <div class="car-name"><?php echo htmlspecialchars($car['name']); ?></div>
            <div class="car-price">IDR <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?>/day</div>
            <div class="car-info">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($car['seats']); ?> seats
                <span style="margin: 0 5px">•</span> 
                <i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?>
            </div>
            <a href="Booking.php?car_id=<?php echo $car['id']; ?>" class="view-btn">View Details & Book</a>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-car"></i>
            <h3>No cars available</h3>
            <p>There are no cars available in this category at the moment.</p>
        </div>
    <?php endif; ?>
</div>

<!-- BRAND ICONS -->
<div class="brands">
    <div class="brand-logo">TOYOTA</div>
    <div class="brand-logo">MERCEDES</div>
    <div class="brand-logo">BMW</div>
    <div class="brand-logo">AUDI</div>
    <div class="brand-logo">HONDA</div>
    <div class="brand-logo">FORD</div>
</div>

<!-- FOOTER -->
<div class="footer">
    <div class="footer-content">
        <div>
            <div class="footer-title">Car Rental</div>
            <p>Premium car rentals for any journey. Fast booking, flexible pickup, and 24/7 support.</p>
        </div>
        <div>
            <div class="footer-title">Location</div>
            <p>2912 Meadowbrook Rd, ZC 123</p>
            <p>Bandung, Indonesia</p>
        </div>
        <div>
            <div class="footer-title">Useful Links</div>
            <p>About Us</p><p>Service</p><p>FAQ</p>
        </div>
        <div>
            <div class="footer-title">Contact</div>
            <p>Email: rent@carrental.com</p>
            <p>Phone: +62 811 0000 000</p>
        </div>
    </div>
    <div class="copy">© Car Rental 2024 — Design by Figm.gen</div>
</div>

</body>
</html>
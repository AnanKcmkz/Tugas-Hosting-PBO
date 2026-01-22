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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us - Car Rental</title>
  <style>
    :root {
      --primary: #5e2ced;
      --secondary: #ffb300;
      --dark: #111;
      --light: #f8f9fa;
      --gray: #6c757d;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      background: #fff; 
      color: #333;
      line-height: 1.6;
    }
    
    header { 
      display: flex; 
      justify-content: space-between; 
      padding: 20px 60px; 
      align-items: center; 
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    .logo {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary);
    }
    
    nav a { 
      margin: 0 15px; 
      text-decoration: none; 
      color: #333; 
      font-weight: 500;
      transition: color 0.3s;
    }
    
    nav a:hover {
      color: var(--primary);
    }
    
    .help-contact {
      text-align: right;
      font-size: 14px;
    }
    
    .help-contact strong {
      color: var(--primary);
    }
    
    .hero {
      text-align: center; 
      padding: 60px 20px;
      background: linear-gradient(rgba(94, 44, 237, 0.8), rgba(94, 44, 237, 0.9)), url('https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      color: white;
    }
    
    .hero h1 {
      font-size: 42px;
      margin-bottom: 10px;
    }
    
    .breadcrumb {
      font-size: 16px;
      opacity: 0.9;
    }
    
    .breadcrumb a {
      color: white;
      text-decoration: none;
    }
    
    .container { 
      width: 90%; 
      max-width: 1200px; 
      margin: auto; 
      padding: 40px 0;
    }
    
    .contact-section { 
      display: flex; 
      gap: 40px; 
      margin-top: 40px; 
      flex-wrap: wrap;
    }
    
    .contact-form { 
      background: var(--light); 
      padding: 30px; 
      border-radius: 10px; 
      flex: 1;
      min-width: 300px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .contact-form h3 {
      margin-bottom: 20px;
      color: var(--primary);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    
    .contact-form input, 
    .contact-form textarea, 
    .contact-form select { 
      width: 100%; 
      padding: 12px; 
      border: 1px solid #ddd; 
      border-radius: 5px; 
      font-family: inherit;
    }
    
    .contact-form textarea {
      height: 120px;
      resize: vertical;
    }
    
    .contact-form button { 
      width: 100%; 
      padding: 12px; 
      background: var(--primary); 
      color: white;
      border: none; 
      border-radius: 5px; 
      cursor: pointer; 
      font-weight: 600;
      font-size: 16px;
      transition: background 0.3s;
    }
    
    .contact-form button:hover {
      background: #4a1fc9;
    }
    
    .contact-info {
      flex: 1;
      min-width: 300px;
    }
    
    .contact-info h3 {
      margin-bottom: 20px;
      color: var(--primary);
    }
    
    .map-container {
      height: 300px;
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .map-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
    
    .info-row { 
      display: flex; 
      justify-content: space-between; 
      margin-top: 40px;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .info-box { 
      text-align: center; 
      padding: 20px;
      background: var(--light);
      border-radius: 10px;
      flex: 1;
      min-width: 200px;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .info-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .info-box i {
      font-size: 30px;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .info-box h4 {
      margin-bottom: 10px;
      color: var(--dark);
    }
    
    .info-box p {
      color: var(--gray);
    }
    
    .section-title {
      text-align: center; 
      margin: 60px 0 30px;
      position: relative;
    }
    
    .section-title:after {
      content: '';
      display: block;
      width: 80px;
      height: 3px;
      background: var(--primary);
      margin: 15px auto;
    }
    
    .blog-row { 
      display: flex; 
      justify-content: space-between; 
      margin-top: 20px; 
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .blog-card { 
      width: 32%; 
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      transition: transform 0.3s;
      min-width: 250px;
      flex: 1;
    }
    
    .blog-card:hover {
      transform: translateY(-5px);
    }
    
    .blog-img {
      height: 180px;
      background-size: cover;
      background-position: center;
    }
    
    .blog-content {
      padding: 20px;
    }
    
    .blog-title { 
      font-size: 18px; 
      margin-bottom: 10px;
      color: var(--dark);
    }
    
    .blog-excerpt {
      color: var(--gray);
      font-size: 14px;
      margin-bottom: 15px;
    }
    
    .read-more {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
    }
    
    footer { 
      background: var(--dark); 
      color: #fff; 
      padding: 60px 40px 30px; 
      margin-top: 80px;
    }
    
    .footer-row { 
      display: flex; 
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 40px;
      margin-bottom: 40px;
    }
    
    .footer-col {
      flex: 1;
      min-width: 200px;
    }
    
    .footer-col h3 {
      margin-bottom: 20px;
      color: var(--secondary);
    }
    
    .footer-col p, .footer-col a {
      color: #aaa;
      margin-bottom: 10px;
      display: block;
      text-decoration: none;
    }
    
    .footer-col a:hover {
      color: white;
    }
    
    .copyright {
      text-align: center;
      padding-top: 30px;
      border-top: 1px solid #333;
      color: #777;
      font-size: 14px;
    }
    
    @media (max-width: 768px) {
      header {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
      }
      
      nav {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
      }
      
      .hero {
        padding: 40px 20px;
      }
      
      .hero h1 {
        font-size: 32px;
      }
      
      .contact-section {
        flex-direction: column;
      }
      
      .info-box, .blog-card {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">🚗 Car Rental</div>
    <nav>
      <a href="Home2.php">Home</a>
      <a href="MenuPilihCar.php">All Cars</a>
      <a href="ContactUs.php">Contact Us</a>
    </nav>
    <div class="help-contact">
      <strong>Need Help?</strong><br>
      +998 547 8850
    </div>
  </header>

  <div class="hero">
    <h1>Contact Us</h1>
    <p class="breadcrumb"><a href="Home2.php">Home</a> / Contact Us</p>
  </div>

  <div class="container">
    <div class="contact-section">
      <div class="contact-form">
        <h3>Send Us a Message</h3>
        <form action="#" method="POST">
          <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" required>
          </div>
          
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
          </div>
          
          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" required>
          </div>
          
          <div class="form-group">
            <label for="message">Your Message</label>
            <textarea id="message" name="message" required></textarea>
          </div>
          
          <button type="submit">Send Message</button>
        </form>
      </div>

      <div class="contact-info">
        <h3>Get In Touch</h3>
        <div class="map-container">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2483.680380487581!2d-0.12775838422990953!3d51.50073217963436!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x487604c38c8cd1d9%3A0xb78f2474b9a45aa9!2sBig%20Ben%2C%20London%2C%20UK!5e0!3m2!1sen!2s!4v1620000000000!5m2!1sen!2s" allowfullscreen="" loading="lazy"></iframe>
        </div>
        
        <p>We're here to help with any questions about our car rental services. Feel free to reach out to us through any of the following methods:</p>
        
        <div class="info-row">
          <div class="info-box">
            <i>📍</i>
            <h4>Location</h4>
            <p>Alexander Rd, London</p>
          </div>
          <div class="info-box">
            <i>✉️</i>
            <h4>Email</h4>
            <p>rental@mycar.com</p>
          </div>
          <div class="info-box">
            <i>📞</i>
            <h4>Phone</h4>
            <p>+987 547 8850</p>
          </div>
          <div class="info-box">
            <i>⏰</i>
            <h4>Opening Hours</h4>
            <p>Mon - Sat: 10am - 10pm</p>
          </div>
        </div>
      </div>
    </div>

    <h2 class="section-title">Latest Blog Posts & News</h2>
    <div class="blog-row">
      <div class="blog-card">
        <div class="blog-img" style="background-image: url('https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')"></div>
        <div class="blog-content">
          <h3 class="blog-title">How To Choose The Right Car</h3>
          <p class="blog-excerpt">Learn how to select the perfect vehicle for your needs and budget.</p>
          <a href="#" class="read-more">Read More →</a>
        </div>
      </div>
      <div class="blog-card">
        <div class="blog-img" style="background-image: url('https://images.unsplash.com/photo-1544636331-e26879cd4d9b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')"></div>
        <div class="blog-content">
          <h3 class="blog-title">Which Plan Is Right For Me?</h3>
          <p class="blog-excerpt">Compare our rental plans to find the one that suits your requirements.</p>
          <a href="#" class="read-more">Read More →</a>
        </div>
      </div>
      <div class="blog-card">
        <div class="blog-img" style="background-image: url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')"></div>
        <div class="blog-content">
          <h3 class="blog-title">Enjoy Speed, Choice & Total Control</h3>
          <p class="blog-excerpt">Discover how our premium services give you complete driving freedom.</p>
          <a href="#" class="read-more">Read More →</a>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-row">
      <div class="footer-col">
        <h3>🚗 Car Rental</h3>
        <p>Your trusted partner for quality car rentals at affordable prices.</p>
      </div>
      <div class="footer-col">
        <h3>Popular Brands</h3>
        <p>Ford | Toyota | Jeep | Audi | BMW | Mercedes</p>
      </div>
      <div class="footer-col">
        <h3>Contact Info</h3>
        <p>Phone: +998 547 8850</p>
        <p>Email: rental@mycar.com</p>
        <p>Address: Alexander Rd, London</p>
      </div>
      <div class="footer-col">
        <h3>Quick Links</h3>
        <a href="Home2.php">Home</a>
        <a href="MenuPilihCar.php">All Cars</a>
        <a href="ContactUs.php">Contact Us</a>
      </div>
    </div>
    <div class="copyright">
      &copy; 2023 Car Rental. All rights reserved.
    </div>
  </footer>
</body>
</html>
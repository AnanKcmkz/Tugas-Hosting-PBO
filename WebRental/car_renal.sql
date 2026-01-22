-- Buat database
CREATE DATABASE IF NOT EXISTS user_management;
USE user_management;

-- Tabel admin
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    full_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel cars
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('Luxury', 'Premium', 'Sport', 'SUV', 'Family', 'Hatchback') NOT NULL,
    price_per_day INT NOT NULL,
    seats INT NOT NULL,
    transmission ENUM('Manual', 'Automatic') NOT NULL,
    fuel_type VARCHAR(50) NOT NULL,
    luggage VARCHAR(50) NOT NULL,
    features TEXT,
    image VARCHAR(255),
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    pickup_location VARCHAR(100) NOT NULL,
    return_location VARCHAR(100) NOT NULL,
    total_days INT NOT NULL,
    total_price INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Insert sample data untuk users (admin)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@carrental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample data untuk cars
INSERT INTO cars (name, category, price_per_day, seats, transmission, fuel_type, luggage, features, image) VALUES
('Toyota Innova', 'Family', 450000, 7, 'Automatic', 'Petrol', '4 bags', 'Air Conditioning, Bluetooth, GPS Navigation, Rear Camera', 'inova.png'),
('Toyota Rush', 'SUV', 480000, 7, 'Automatic', 'Petrol', '3 bags', 'Air Conditioning, Bluetooth, GPS Navigation, Sunroof', 'rush.jpg'),
('Toyota Yaris', 'Hatchback', 350000, 5, 'Automatic', 'Petrol', '2 bags', 'Air Conditioning, Bluetooth, GPS Navigation', 'yaris.png'),
('Mercedes S-Class', 'Luxury', 1500000, 5, 'Automatic', 'Premium', '2 bags', 'Leather Seats, Sunroof, Premium Sound System, Heated Seats', 's-class.jpg'),
('Mercedes B-Class', 'Premium', 1200000, 5, 'Automatic', 'Premium', '2 bags', 'Leather Seats, Sunroof, Premium Sound System', 'B-class.jpg'),
('BMW M5', 'Sport', 950000, 5, 'Automatic', 'Premium', '2 bags', 'Sport Seats, Sunroof, Premium Sound System, Sport Suspension', 'm5.jpg'),
('Honda Civic', 'Premium', 550000, 5, 'Automatic', 'Petrol', '2 bags', 'Air Conditioning, Bluetooth, Sunroof, Rear Camera', 'civic.jpg'),
('Toyota Fortuner', 'SUV', 650000, 7, 'Automatic', 'Diesel', '4 bags', 'Air Conditioning, Bluetooth, GPS, 4WD', 'fortuner.jpg'),
('Daihatsu Ayla', 'Hatchback', 250000, 5, 'Manual', 'Petrol', '1 bag', 'Air Conditioning, Bluetooth', 'ayla.jpg'),
('Lexus RX', 'Luxury', 1800000, 5, 'Automatic', 'Premium', '3 bags', 'Leather Seats, Sunroof, Premium Sound, Navigation', 'lexus-rx.jpg');
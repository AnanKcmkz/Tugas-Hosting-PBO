<?php
session_start();

// KONEKSI DATABASE
$host = "localhost";
$username = "root";
$password = "";
$dbname = "user_management";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// PROSES LOGIN
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];
    
    // Query untuk mencari user
    $sql = "SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password_hash'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            header("Location: Home2.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username/Email tidak ditemukan!";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - RentalMobil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 1000px;
            max-width: 95%;
            height: 600px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            overflow: hidden;
            background: white;
        }

        .background-section {
            flex: 1.2;
            background: linear-gradient(rgba(30, 60, 114, 0.85), rgba(42, 82, 152, 0.85)), 
                        url('https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            position: relative;
        }

        .background-content {
            text-align: center;
            max-width: 90%;
            z-index: 2;
        }

        .background-content h2 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .background-content p {
            font-size: 1.2rem;
            line-height: 1.7;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo span {
            color: #ffb238;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .feature i {
            font-size: 1.5rem;
            color: #ffb238;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 50%;
        }

        .form-section {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .form-container {
            width: 100%;
        }

        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            color: #333;
            font-weight: 700;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #555;
            font-size: 1rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 1.1rem;
        }

        .input-with-icon input {
            padding-left: 45px;
        }

        input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        input:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
            outline: none;
            background: white;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.4);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 25px;
            color: #6b7280;
            font-size: 1rem;
        }

        .form-footer a {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #dc2626;
            font-weight: 600;
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #1e3c72;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                height: auto;
                width: 95%;
            }
            
            .background-section {
                padding: 30px 20px;
                min-height: 300px;
            }
            
            .background-content h2 {
                font-size: 2.2rem;
            }
            
            .form-section {
                padding: 40px 30px;
            }
        }

        @media (max-width: 480px) {
            .background-content h2 {
                font-size: 1.8rem;
            }
            
            .background-content p {
                font-size: 1rem;
            }
            
            .form-section {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="background-section">
            <div class="background-content">
                <div class="logo">Rental<span>Mobil</span></div>
                <h2>Selamat Datang Kembali!</h2>
                <p>Masuk ke akun Anda untuk mengakses layanan rental mobil terbaik dan melanjutkan perjalanan Anda.</p>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-car"></i>
                        <span>Beragam jenis mobil terbaru</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Layanan di seluruh Indonesia</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Keamanan dan kenyamanan terjamin</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <div class="form-container">
                <h1>Masuk ke Akun</h1>
                <p class="subtitle">Masuk untuk mengakses akun Anda</p>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="identifier">Email atau Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="identifier" name="identifier" placeholder="Masukkan email atau username" required value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                        <div class="forgot-password">
                            <a href="#">Lupa password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </form>
                
                <div class="form-footer">
                    Belum punya akun? <a href="register.php">Daftar di sini</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
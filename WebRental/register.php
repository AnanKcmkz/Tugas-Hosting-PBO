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

// PROSES REGISTER
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif (strlen($password) < 8) {
        $error = "Password harus minimal 8 karakter!";
    } else {
        // Cek apakah username/email sudah ada
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username atau Email sudah terdaftar!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $insert_sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $username, $email, $password_hash);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
                header("Location: Login.php");
                exit();
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
            
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - DepositPhotos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
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
            background: linear-gradient(rgba(106, 17, 203, 0.8), rgba(37, 117, 252, 0.8)), 
                        url('https://images.unsplash.com/photo-1557683316-973673baf926?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1129&q=80') center/cover no-repeat;
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
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
            outline: none;
            background: white;
        }

        .password-requirements {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .requirement i {
            margin-right: 8px;
            font-size: 0.8rem;
        }

        .requirement.met {
            color: #10b981;
        }

        .requirement.unmet {
            color: #6b7280;
        }

        .password-strength {
            margin-top: 10px;
            height: 6px;
            border-radius: 10px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.4s ease;
            border-radius: 10px;
        }

        .strength-weak {
            background: #ef4444;
            width: 33%;
        }

        .strength-medium {
            background: #f59e0b;
            width: 66%;
        }

        .strength-strong {
            background: #10b981;
            width: 100%;
        }

        .strength-text {
            margin-top: 5px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .strength-text.weak {
            color: #ef4444;
        }

        .strength-text.medium {
            color: #f59e0b;
        }

        .strength-text.strong {
            color: #10b981;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(106, 17, 203, 0.4);
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
            color: #6a11cb;
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

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #10b981;
            font-weight: 600;
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
                <div class="logo">Meta<span>Rental</span></div>
                <h2>Selamat Datang!</h2>
                <p>Silahkan Daftar kan akun Anda untuk mengakses layanan rental mobil terbaik dan melanjutkan perjalanan Anda.</p>
                
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
                <h1>Buat Akun Baru</h1>
                <p class="subtitle">Isi informasi di bawah ini untuk membuat akun</p>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Nama Pengguna</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" placeholder="Masukkan nama pengguna" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Anda</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Masukkan alamat email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Buat Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Buat password yang kuat" required onkeyup="checkPasswordStrength()">
                        </div>
                        
                        <div class="password-requirements">
                            <div class="requirement unmet" id="length-req">
                                <i class="far fa-circle"></i> Minimal 8 karakter
                            </div>
                            <div class="requirement unmet" id="case-req">
                                <i class="far fa-circle"></i> Mengandung huruf besar & kecil
                            </div>
                            <div class="requirement unmet" id="number-req">
                                <i class="far fa-circle"></i> Mengandung angka
                            </div>
                            <div class="requirement unmet" id="special-req">
                                <i class="far fa-circle"></i> Mengandung karakter khusus
                            </div>
                        </div>
                        
                        <div class="password-strength">
                            <div class="strength-bar" id="password-strength-bar"></div>
                        </div>
                        <div class="strength-text" id="password-strength-text">Kekuatan password</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password" required onkeyup="checkPasswordMatch()">
                        </div>
                        <div id="password-match" style="margin-top: 5px; font-size: 0.85rem;"></div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> Buat Akun
                    </button>
                </form>
                
                <div class="form-footer">
                    Sudah punya akun? <a href="Login.php">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            // Reset
            strengthBar.className = 'strength-bar';
            strengthText.className = 'strength-text';
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = 'Kekuatan password';
                updateRequirements([false, false, false, false]);
                return;
            }
            
            // Hitung kekuatan password
            let strength = 0;
            const requirements = [false, false, false, false];
            
            // Panjang password
            if (password.length >= 8) {
                strength += 25;
                requirements[0] = true;
            }
            
            // Huruf kecil dan besar
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
                strength += 25;
                requirements[1] = true;
            }
            
            // Angka
            if (/[0-9]/.test(password)) {
                strength += 25;
                requirements[2] = true;
            }
            
            // Karakter khusus
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 25;
                requirements[3] = true;
            }
            
            // Update tampilan
            if (strength <= 25) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Password Lemah';
                strengthText.classList.add('weak');
            } else if (strength <= 50) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Password Cukup';
                strengthText.classList.add('medium');
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Password Kuat';
                strengthText.classList.add('strong');
            }
            
            updateRequirements(requirements);
        }
        
        function updateRequirements(requirements) {
            const reqIds = ['length-req', 'case-req', 'number-req', 'special-req'];
            const icons = ['fa-check-circle', 'fa-circle'];
            
            reqIds.forEach((id, index) => {
                const element = document.getElementById(id);
                if (requirements[index]) {
                    element.classList.remove('unmet');
                    element.classList.add('met');
                    element.innerHTML = `<i class="fas fa-check-circle"></i> ${element.textContent.split(' ').slice(1).join(' ')}`;
                } else {
                    element.classList.remove('met');
                    element.classList.add('unmet');
                    element.innerHTML = `<i class="far fa-circle"></i> ${element.textContent.split(' ').slice(1).join(' ')}`;
                }
            });
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchElement = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchElement.textContent = '';
                matchElement.style.color = '';
            } else if (password === confirmPassword) {
                matchElement.innerHTML = '<i class="fas fa-check-circle"></i> Password cocok';
                matchElement.style.color = '#10b981';
            } else {
                matchElement.innerHTML = '<i class="fas fa-times-circle"></i> Password tidak cocok';
                matchElement.style.color = '#ef4444';
            }
        }
    </script>
</body>
</html>
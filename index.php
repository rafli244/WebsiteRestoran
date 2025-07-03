<?php
session_start();
include("koneksi.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Using MD5

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $query = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($query) == 1) {
        $_SESSION['user'] = $username;
        header("Location: index1.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
    mysqli_close($koneksi);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir - Sistem Restoran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            overflow: hidden;
            height: 100vh;
            position: relative;
            color: #fff;
        }
        
        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
        
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float linear infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        .login-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        
        .system-info {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 15px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 10px 15px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .info-item i {
            font-size: 16px;
            color: #d1b3ff;
        }
        
        .login-box {
            background: rgba(106, 27, 154, 0.8);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 450px; /* Lebarkan box login */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: rgba(123, 31, 162, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #e1bee7;
            border: 2px solid rgba(225, 190, 231, 0.3);
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 5px;
            color: #fff;
            font-weight: 600;
        }
        
        .subtitle {
            font-size: 14px;
            color: rgba(225, 190, 231, 0.8);
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: rgba(225, 190, 231, 0.9);
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s;
            border: 1px solid rgba(225, 190, 231, 0.2);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #ba68c8;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(186, 104, 200, 0.2);
        }
        
        .input-group input::placeholder {
            color: rgba(225, 190, 231, 0.5);
        }
        
        .error-message {
            color: #ff9e9e;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #9c27b0, #7b1fa2);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(156, 39, 176, 0.4);
        }
        
        .forgot-password {
            margin-top: 20px;
            font-size: 13px;
        }
        
        .forgot-password a {
            color: rgba(225, 190, 231, 0.7);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .forgot-password a:hover {
            color: #e1bee7;
        }
    </style>
</head>

<body>
    <video class="video-background" autoplay muted loop>
        <source src="Restaurant Ad Video Template (Editable).mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="system-info">
            <div class="info-item">
                <i class="fas fa-user-circle"></i>
                <span>Kasir</span>
            </div>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>Shift</span> <span class="current-time"><?= date('H:i') ?></span>
            </div>
        </div>
        <div class="login-box">
            <div class="logo">
                <i class="fas fa-utensils"></i>
            </div>
            <h1>Double Box</h1>
            <p class="subtitle">Sistem Manajemen Restoran</p>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan Username" required autofocus>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
                </div>
                <?php if (isset($error)): ?>
                    <p class="error-message"><?php echo $error; ?></p>
                <?php endif; ?>
                <button type="submit" class="login-button">Login</button>
            </form>
            <p class="forgot-password">
                <a href="#">Lupa Password?</a>
            </p>
        </div>
    </div>

    
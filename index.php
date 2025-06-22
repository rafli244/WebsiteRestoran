<?php
session_start();
include("koneksi.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // memakai MD5, bukan hash modern

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");

    if (mysqli_num_rows($query) == 1) {
        $_SESSION['user'] = $username;
        header("Location: index1.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir - Sistem Restoran</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Video Background */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            object-fit: cover;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideInUp 0.8s ease-out;
            opacity: 90%;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .login-header h2 {
            color: #333;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-header .subtitle {
            color: #666;
            font-size: 16px;
            font-weight: 400;
        }

        .cashier-badge {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #555;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 18px 25px;
            border: 2px solid #e1e5e9;
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-group input:hover {
            border-color: #c3c8cf;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #e74c3c;
            font-size: 14px;
            font-weight: 500;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 20%, 40%, 60%, 80% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
        }

        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 20px;
        }

        .form-group.has-icon input {
            padding-right: 60px;
        }

        .system-info {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .system-info .info-item {
            display: inline-block;
            margin: 0 15px;
            color: #666;
            font-size: 13px;
        }

        .system-info .info-item span {
            color: #667eea;
            font-weight: 600;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #999;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 35px 25px;
                margin: 10px;
            }
            
            .login-header h2 {
                font-size: 28px;
            }

            .login-header .logo {
                width: 70px;
                height: 70px;
                font-size: 32px;
            }
        }

        /* Loading animation */
        .loading {
            display: none;
            width: 22px;
            height: 22px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-loading {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Floating particles effect */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <!-- Video Background -->
    <video class="video-background" autoplay muted loop>
        <source src="High Wycombe.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="video-overlay"></div>

    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo">🍽️</div>
            <div class="cashier-badge">SISTEM KASIR</div>
            <h2>Login Kasir</h2>
            <p class="subtitle">Masuk ke sistem point of sale restoran</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" id="loginForm">
            <div class="form-group has-icon">
                <label for="username">Username Kasir</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Masukkan username kasir"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <span class="input-icon">👤</span>
            </div>

            <div class="form-group has-icon">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Masukkan password">
                <span class="input-icon">🔒</span>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <span class="loading" id="loading"></span>
                <span id="btnText">🚀 Masuk ke Sistem Kasir</span>
            </button>
        </form>

        <div class="login-footer">
            &copy; 2024 Sistem Restoran POS. All rights reserved.
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                
                particlesContainer.appendChild(particle);
            }
        }

        // Loading animation saat form di-submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            const btnText = document.getElementById('btnText');
            
            btn.disabled = true;
            btn.classList.add('btn-loading');
            loading.style.display = 'inline-block';
            btnText.textContent = 'Memverifikasi akses...';
        });

        // Auto focus pada input username
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
            createParticles();
        });

        // Enter key navigation
        document.getElementById('username').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('password').focus();
            }
        });

        // Add typing effect to subtitle
        const subtitle = document.querySelector('.subtitle');
        const originalText = subtitle.textContent;
        subtitle.textContent = '';
        
        let i = 0;
        function typeWriter() {
            if (i < originalText.length) {
                subtitle.textContent += originalText.charAt(i);
                i++;
                setTimeout(typeWriter, 50);
            }
        }
        
        setTimeout(typeWriter, 1000);

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            const shiftElement = document.querySelector('.system-info .info-item:last-child span');
            if (shiftElement && shiftElement.nextSibling) {
                shiftElement.nextSibling.textContent = ' ' + timeString;
            }
        }

        setInterval(updateTime, 1000);
    </script>
</body>

</html>
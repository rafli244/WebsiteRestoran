<?php
session_start();
include("koneksi.php"); // Panggil koneksi di awal

$message = ''; // Untuk menampilkan pesan

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST["tanggal"]);
    $total_keuntungan = (float)$_POST["total_keuntungan"]; 

    $sql = "INSERT INTO transaksi (tanggal, total_keuntungan) VALUES ('$tanggal', $total_keuntungan)";
    if (mysqli_query($koneksi, $sql)) {
        $message = '<p class="success-message">Data transaksi berhasil ditambahkan.</p>';
    } else {
        $message = '<p class="error-message">Error: ' . mysqli_error($koneksi) . '</p>';
    }
}
mysqli_close($koneksi); // Tutup koneksi setelah selesai
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index1.css">
    <title>Input Transaksi</title>
    <style>
        .container {
            padding: 20px;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container form {
            display: inline-block;
            text-align: left;
        }

        .container input[type="date"],
        .container input[type="number"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .container button {
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .container button:hover {
            background-color: #6a49b6;
        }
        .message-container {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '_sidebar.php'; // Sertakan sidebar ?>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Input Transaksi Baru</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="add-transaksi-section" class="container">
                <h2>Input Transaksi Baru</h2>
                <?php if ($message): ?>
                    <div class="message-container <?php echo strpos($message, 'success') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="input_transaksi.php">
                    <label for="tanggal">Tanggal:</label>
                    <input type="date" name="tanggal" required value="<?= htmlspecialchars(date('Y-m-d')) ?>"><br><br>
                    <label for="total_keuntungan">Total Keuntungan:</label>
                    <input type="number" name="total_keuntungan" step="0.01" placeholder="Total keuntungan" required min="0"><br><br>
                    <button type="submit">Simpan</button>
                </form>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = window.location.pathname.split('/').pop();
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
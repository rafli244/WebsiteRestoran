<?php
session_start();
include 'koneksi.php'; // Panggil koneksi di awal

$message = ''; // Untuk menampilkan pesan

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_transaksi = (int)$_POST['id_transaksi'];
    $id_menu = (int)$_POST['id_menu'];
    $jumlah = (int)$_POST['jumlah'];
    $subtotal = (float)$_POST['subtotal'];

    $sql = "INSERT INTO detail_transaksi (id_transaksi, id_menu, jumlah, subtotal) VALUES ($id_transaksi, $id_menu, $jumlah, $subtotal)";
    if (mysqli_query($koneksi, $sql)) {
        $message = '<p class="success-message">Data detail transaksi berhasil ditambahkan.</p>';
    } else {
        $message = '<p class="error-message">Error: ' . mysqli_error($koneksi) . '</p>';
    }
}

// Ambil data untuk dropdown
$transaksi_options = [];
$result_trx = mysqli_query($koneksi, "SELECT id_transaksi, tanggal FROM transaksi ORDER BY tanggal DESC, id_transaksi DESC");
if ($result_trx) {
    while ($row = mysqli_fetch_assoc($result_trx)) {
        $transaksi_options[] = $row;
    }
} else {
    error_log("Failed to load transactions: " . mysqli_error($koneksi));
    $message = '<p class="error-message">Gagal memuat daftar transaksi.</p>';
}

$menu_options = [];
$result_menu = mysqli_query($koneksi, "SELECT id_menu, nama_menu FROM menu ORDER BY nama_menu ASC");
if ($result_menu) {
    while ($row = mysqli_fetch_assoc($result_menu)) {
        $menu_options[] = $row;
    }
} else {
    error_log("Failed to load menus: " . mysqli_error($koneksi));
    $message = '<p class="error-message">Gagal memuat daftar menu.</p>';
}

mysqli_close($koneksi); // Tutup koneksi setelah semua data diambil dan sebelum HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index1.css">
    <title>Input Detail Transaksi</title>
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

        .container input[type="text"],
        .container input[type="number"],
        .container select {
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
                <h1 class="page-title">Input Detail Transaksi</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="add-detail-transaksi-section" class="container">
                <h2>Input Detail Transaksi</h2>
                <?php if ($message): ?>
                    <div class="message-container <?php echo strpos($message, 'success') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="input_detail_transaksi.php">
                    <label for="id_transaksi">ID Transaksi:</label>
                    <select name="id_transaksi" id="id_transaksi">
                        <?php if (empty($transaksi_options)): ?>
                            <option value="">Tidak ada transaksi tersedia</option>
                        <?php else: ?>
                            <?php foreach ($transaksi_options as $trx): ?>
                                <option value='<?= htmlspecialchars($trx['id_transaksi']) ?>'>ID: <?= htmlspecialchars($trx['id_transaksi']) ?> - <?= htmlspecialchars($trx['tanggal']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select><br><br>

                    <label for="id_menu">Menu:</label>
                    <select name="id_menu" id="id_menu">
                        <?php if (empty($menu_options)): ?>
                            <option value="">Tidak ada menu tersedia</option>
                        <?php else: ?>
                            <?php foreach ($menu_options as $menu): ?>
                                <option value='<?= htmlspecialchars($menu['id_menu']) ?>'><?= htmlspecialchars($menu['nama_menu']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select><br><br>

                    <label for="jumlah">Jumlah:</label>
                    <input type="number" name="jumlah" placeholder="Jumlah" required min="1"><br><br>

                    <label for="subtotal">Subtotal:</label>
                    <input type="number" step="0.01" name="subtotal" placeholder="Subtotal" required><br><br>

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
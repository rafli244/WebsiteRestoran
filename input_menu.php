<?php
session_start();
include 'koneksi.php';

$message = ''; // Untuk menampilkan pesan sukses/gagal

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_menu = mysqli_real_escape_string($koneksi, $_POST['nama_menu'] ?? '');
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga'] ?? '');
    $id_jenis = (int)$_POST['id_jenis'] ?? 0; // Pastikan default 0 jika tidak ada
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi'] ?? '');
    $gambar = mysqli_real_escape_string($koneksi, $_POST['gambar'] ?? ''); // Ambil langsung dari POST

    // Validasi input
    if (empty($nama_menu) || empty($harga) || empty($id_jenis)) {
        $message = '<p class="error-message">Nama menu, harga, dan jenis menu harus diisi.</p>';
    } elseif (!is_numeric($harga) || $harga < 0) {
        $message = '<p class="error-message">Harga harus berupa angka positif.</p>';
    } else {
        // Logika upload file DIBUANG. Sekarang hanya mengambil URL dari input teks.
        // Tidak perlu penanganan $_FILES lagi.

        // Jika gambar URL wajib diisi, tambahkan validasi ini:
        // if (empty($gambar)) {
        //     $message = '<p class="error-message">Link gambar harus diisi.</p>';
        // }

        if (empty($message)) { // Lanjutkan jika tidak ada error validasi
            // Pastikan kolom 'deskripsi' dan 'gambar' ada di tabel 'menu'
            $sql = "INSERT INTO menu (nama_menu, harga, id_jenis, deskripsi, gambar) VALUES ('$nama_menu', $harga, $id_jenis, '$deskripsi', '$gambar')";
            if (mysqli_query($koneksi, $sql)) {
                $message = '<p class="success-message">Menu berhasil ditambahkan.</p>';
                // Opsional: kosongkan form setelah sukses
                $_POST = array();
            } else {
                $message = '<p class="error-message">Gagal menambahkan menu ke database: ' . mysqli_error($koneksi) . '</p>';
            }
        }
    }
}

// Ambil jenis menu untuk dropdown
$jenis_menu = [];
$result_jenis = mysqli_query($koneksi, "SELECT id_jenis, nama_jenis FROM jenis_menu ORDER BY nama_jenis ASC");
if ($result_jenis) {
    while ($row = mysqli_fetch_assoc($result_jenis)) {
        $jenis_menu[] = $row;
    }
} else {
    error_log("Failed to load menu types: " . mysqli_error($koneksi));
}

mysqli_close($koneksi);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu – Double Box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index1.css">
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
        .container textarea,
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
                <h1 class="page-title">Tambah Menu Makanan</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="add-menu-section" class="container">
                <h2>Tambah Menu Baru</h2>
                <?php if ($message): ?>
                    <div class="message-container <?php echo strpos($message, 'success') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="input_menu.php" method="POST" enctype="multipart/form-data">
                    <label for="nama_menu">Nama Menu:</label><br>
                    <input type="text" id="nama_menu" name="nama_menu" required value="<?= htmlspecialchars($_POST['nama_menu'] ?? '') ?>"><br>

                    <label for="harga">Harga:</label><br>
                    <input type="number" id="harga" name="harga" step="0.01" required value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>"><br>

                    <label for="id_jenis">Jenis Menu:</label><br>
                    <select id="id_jenis" name="id_jenis" required>
                        <option value="">Pilih Jenis Menu</option>
                        <?php foreach ($jenis_menu as $jenis): ?>
                            <option value="<?= htmlspecialchars($jenis['id_jenis']) ?>" <?= (isset($_POST['id_jenis']) && $_POST['id_jenis'] == $jenis['id_jenis']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($jenis['nama_jenis']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br>

                    <label for="deskripsi">Deskripsi:</label><br>
                    <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Opsional: Deskripsi singkat tentang menu ini"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea><br>

                    <label for="gambar">Link Gambar Menu (Opsional):</label><br>
                    <input type="text" id="gambar" name="gambar" placeholder="Contoh: https://example.com/gambar.jpg" value="<?= htmlspecialchars($_POST['gambar'] ?? '') ?>"><br><br>

                    <button type="submit">Tambah Menu</button>
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
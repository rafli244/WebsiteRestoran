<?php
session_start();
include 'koneksi.php';

$message = ''; // Untuk menampilkan pesan sukses/gagal

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_jenis']);
    
    // Menggunakan mysqli_query langsung
    $sql = "INSERT INTO jenis_menu (nama_jenis) VALUES ('$nama')";
    if (mysqli_query($koneksi, $sql)) {
        $message = '<p class="success-message">Data jenis menu berhasil ditambahkan.</p>';
    } else {
        $message = '<p class="error-message">Error: ' . mysqli_error($koneksi) . '</p>';
    }
}
// Tidak perlu menutup koneksi di sini, karena file lain mungkin masih memerlukannya jika di-include
// Namun, jika ini adalah akhir dari skrip utama, bisa ditutup. Untuk konsistensi, saya biarkan terbuka di sini.
// mysqli_close($koneksi);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jenis Baru – Double Box</title>
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

        .container input[type="text"] {
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
                <h1 class="page-title" id="page-title">Tambah Jenis Menu</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="add-jenis-menu-section" class="container">
                <h2>Tambah Jenis Menu Baru</h2>
                <?php if ($message): ?>
                    <div class="message-container <?php echo strpos($message, 'success') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="input_jenis_menu.php" method="POST">
                    <label for="nama_jenis">Nama Jenis Menu:</label><br>
                    <input type="text" id="nama_jenis" name="nama_jenis" required><br><br>
                    <button type="submit">Simpan</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        // JS navigasi yang sudah tidak relevan dengan struktur ini dihilangkan.
        // Penentuan kelas 'active' pada sidebar akan dilakukan secara manual atau dengan PHP
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
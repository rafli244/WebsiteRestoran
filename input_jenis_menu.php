<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jenis Baru – Double Box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/index1.css">
    <style>
        /* Tambahkan style CSS yang diperlukan disini */
        .container {
            padding: 20px;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container form {
            display: inline-block;
            text-align: auto;
        }

        .container input[type="text"] {
            width: calc(106% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .container button {
            width: 100%;
            padding: 10px;
            background-color: #6f42c1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .container button:hover {
            background-color: #5a3197;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo"><i class="fas fa-utensils"></i></div>
                <div class="brand-name">Double Box</div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index1.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="Kasir.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Kasir</span></a></li>
                <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-list"></i><span>Jenis Menu</span></a></li>
                <li class="nav-item">
                    <a href="input_menu.php" class="nav-link">
                        <i class="fas fa-hamburger"></i><span>Menu Makanan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="input_booking.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i><span>Booking</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tampil.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i><span>Laporan</span>
                    </a>
                </li>
            </ul>

        </aside>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Tambah Jenis Menu Baru</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Admin</span>
                </div>
            </div>

            <section id="jenis-menu-section">
                <div class="container">
                    <h2>Tambah Jenis Baru</h2>
                    <form method="POST">
                        <input type="text" name="nama_jenis" placeholder="Jenis menu (cth: Utama)" required>
                        <button type="submit">Simpan</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        /* ========= NAVIGATION ========= */
        document.querySelectorAll('.nav-link[data-target]').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const target = link.dataset.target;
                if (!target) return;

                document.querySelectorAll('main section').forEach(sec => sec.style.display = 'none');
                document.getElementById(target).style.display = 'block';
                document.getElementById('page-title').textContent = target === 'dashboard-section' ? 'Dashboard Admin' : 'Tambah Jenis Baru';
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });

        /* ========= INIT ========= */
        document.addEventListener('DOMContentLoaded', () => {
            const currentSection = document.querySelector('main section:visible');
            if (currentSection) {
                document.querySelector(`.nav-link[data-target="${currentSection.id}"]`).classList.add('active');
            }
        });
    </script>

    <?php
    include 'koneksi.php';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama = $_POST['nama_jenis'];
        mysqli_query($koneksi, "INSERT INTO jenis_menu (nama_jenis) VALUES ('$nama')");
        echo "<script>alert('Data jenis menu berhasil ditambahkan.');</script>";
        // Redirect to prevent form resubmission on refresh
        // echo "<script>window.location.href='input_jenis_menu.php';</script>";
    }
    ?>
</body>

</html>
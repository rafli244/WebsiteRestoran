<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu</title>
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
        .container select {
            width: calc(100% - 22px);
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
                <li class="nav-item"><a href="index1.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="kasir.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Kasir</span></a></li>
                <li class="nav-item"><a href="input_jenis_menu.php" class="nav-link"><i class="fas fa-list"></i><span>Jenis Menu</span></a></li>
                <li class="nav-item"><a href="input_menu.php" class="nav-link active"><i class="fas fa-hamburger"></i><span>Menu Makanan</span></a></li>
                <li class="nav-item"><a href="input_booking.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Booking</span></a></li>
                <li class="nav-item"><a href="tampil.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Tambah Menu</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Admin</span>
                </div>
            </div>

            <section id="add-menu-section">
                <div class="container">
                    <h2>Tambah Menu</h2>
                    <form id="add-menu-form" method="POST">
                        <div class="form-group">
                            <label for="nama_menu">Nama Menu:</label>
                            <input type="text" id="nama_menu" name="nama_menu" placeholder="Nama menu" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="harga">Harga:</label>
                            <input type="number" id="harga" name="harga" placeholder="Harga" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_jenis">Jenis Menu:</label>
                            <select id="id_jenis" name="id_jenis">
                                <?php
                                $jenis = mysqli_query($koneksi, "SELECT id_jenis, nama_jenis FROM jenis_menu ORDER BY nama_jenis ASC");
                                
                                if ($jenis && mysqli_num_rows($jenis) > 0) {
                                    while ($row_jenis = mysqli_fetch_assoc($jenis)) {
                                        echo "<option value='{$row_jenis['id_jenis']}'>{$row_jenis['nama_jenis']}</option>";
                                    }
                                } else {
                                    echo "<option value=''>Tidak ada jenis menu tersedia</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit">Tambah Menu</button>
                    </form>

                    <div id="response-message"></div>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('add-menu-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });

            try {
                const response = await fetch('api/input_menu.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                document.getElementById('response-message').innerHTML = `<p class="${result.status === 'success' ? 'success-message' : 'error-message'}">${result.message}</p>`;
                if (result.status === 'success') {
                    e.target.reset();
                }
            } catch (error) {
                document.getElementById('response-message').innerHTML = `<p class="error-message">Terjadi kesalahan jaringan saat menambahkan menu. Silakan coba lagi.</p>`;
                console.error('Network error during menu addition:', error);
            }
        });
    </script>
</body>
</html>
<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Kasir</span></a></li>
                <li class="nav-item"><a href="input_jenis_menu.php" class="nav-link"><i class="fas fa-list"></i><span>Jenis Menu</span></a></li>
                <li class="nav-item"><a href="input_menu.php" class="nav-link active"><i class="fas fa-hamburger"></i><span>Menu Makanan</span></a></li>
                <li class="nav-item"><a href="input_booking.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Booking</span></a></li>
                <li class="nav-item"><a href="tampil.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Edit Menu</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Admin</span>
                </div>
            </div>

            <section id="edit-menu-section">
                <div class="container">
                    <h2>Edit Menu</h2>
                    <form method="POST">
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
                                $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                                $menu = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu = '$edit_id'");
                                $row = mysqli_fetch_assoc($menu);

                                $jenis = mysqli_query($koneksi, "SELECT id_jenis, nama_jenis FROM jenis_menu ORDER BY nama_jenis ASC");
                                
                                if ($jenis && mysqli_num_rows($jenis) > 0) {
                                    while ($row_jenis = mysqli_fetch_assoc($jenis)) {
                                        echo "<option value='{$row_jenis['id_jenis']}'" . ($row['id_jenis'] == $row_jenis['id_jenis'] ? " selected" : "") . ">{$row_jenis['nama_jenis']}</option>";
                                    }
                                } else {
                                    echo "<option value=''>Tidak ada jenis menu tersedia</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <input type="hidden" name="id_menu" value="<?php echo $edit_id; ?>">
                        <button type="submit">Update Menu</button>
                    </form>

                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $id_menu = $_POST["id_menu"];
                        $nama = mysqli_real_escape_string($koneksi, $_POST["nama_menu"]);
                        $harga = mysqli_real_escape_string($koneksi, $_POST["harga"]);
                        $id_jenis = mysqli_real_escape_string($koneksi, $_POST["id_jenis"]);

                        if (empty($nama) || empty($harga) || empty($id_jenis)) {
                            echo "<p class='error-message'>Semua field harus diisi.</p>";
                        } elseif (!is_numeric($harga) || $harga < 0) {
                            echo "<p class='error-message'>Harga harus berupa angka positif.</p>";
                        } else {
                            $sql_update = "UPDATE menu SET nama_menu='$nama', harga='$harga', id_jenis='$id_jenis' WHERE id_menu='$id_menu'";
                            
                            if (mysqli_query($koneksi, $sql_update)) {
                                echo "<p class='success-message'>Data menu berhasil diupdate.</p>";
                            } else {
                                echo "<p class='error-message'>Error updating data menu: " . mysqli_error($koneksi) . "</p>";
                            }
                        }
                    }
                    ?>
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
                document.getElementById('page-title').textContent = target === 'dashboard-section' ? 'Dashboard Admin' : 'Edit Menu';
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
</body>
</html>
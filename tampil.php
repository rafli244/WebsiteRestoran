<?php
date_default_timezone_set('Asia/Jakarta');
include 'koneksi.php';          // koneksi DB

$tanggal_hari_ini = date('Y-m-d');

/* ---------- QUERY RINGAN (karena sudah ada INDEX) ---------- */
function getSingleResult($koneksi, $sql) {
    $r = $koneksi->query($sql);
    return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}

/* 1. Menu Global Terfavorit – view lama -------------------------------- */
$menu_favorit   = getSingleResult($koneksi,
    "SELECT nama_menu, total_dipesan
     FROM menufavorit
     ORDER BY total_dipesan DESC LIMIT 1");

/* 2. Termahal & Termurah --------------------------------------------- */
$menu_termahal  = getSingleResult($koneksi,
    "SELECT nama_menu, harga FROM menu ORDER BY harga DESC LIMIT 1");
$menu_termurah  = getSingleResult($koneksi,
    "SELECT nama_menu, harga FROM menu ORDER BY harga ASC  LIMIT 1");

/* 3. Menu paling tidak diminati -------------------------------------- */
$menu_tidak_diminati = getSingleResult($koneksi, "
    SELECT m.nama_menu,
           COALESCE(SUM(dt.jumlah),0) AS total_dipesan
    FROM menu m
    LEFT JOIN detail_transaksi dt ON m.id_menu = dt.id_menu
    GROUP BY m.id_menu
    ORDER BY total_dipesan ASC, m.nama_menu
    LIMIT 1");

/* 4. Jenis menu paling laku ------------------------------------------ */
$jenis_menu_populer = getSingleResult($koneksi, "
    SELECT jm.nama_jenis,
           SUM(dt.jumlah) AS total_dipesan
    FROM detail_transaksi dt
    JOIN menu m  ON dt.id_menu = m.id_menu
    JOIN jenis_menu jm ON m.id_jenis = jm.id_jenis
    GROUP BY jm.id_jenis
    ORDER BY total_dipesan DESC
    LIMIT 1");

/* 5. Menu favorit *per jenis*  (dipelihara trigger + index) ---------- */
$favorit_per_jenis = [];
$sqlFavJenis = "
    SELECT jm.nama_jenis,
           m.nama_menu,
           mf.total_dipesan,
           total_dipesan_menu(m.id_menu) AS total_semua_waktu  -- pakai FUNCTION
    FROM menufavorit_per_jenis mf
    JOIN jenis_menu jm ON jm.id_jenis = mf.id_jenis
    JOIN menu m       ON m.id_menu   = mf.id_menu
    ORDER BY jm.nama_jenis";
if ($q = $koneksi->query($sqlFavJenis)) {
    while ($row = $q->fetch_assoc()) $favorit_per_jenis[] = $row;
}

/* 6. Penjualan hari ini ---------------------------------------------- */
$total_hari_ini = getSingleResult($koneksi, "
    SELECT COALESCE(SUM(subtotal),0) AS total_penjualan
    FROM detail_transaksi d
    JOIN transaksi t ON d.id_transaksi = t.id_transaksi
    WHERE t.tanggal = '$tanggal_hari_ini'");

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analisis Penjualan</title>
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

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: left;
        }

        .card-header {
            padding: 15px 20px;
            font-weight: 600;
            color: #fff;
            background-color: #6f42c1;

        }

        .card-body {
            padding: 20px;
            color: #333;
        }


        .card-primary .card-header {
            background-color: #6f42c1;
        }

        .card-accent-success .card-header {
            background-color: #28a745;
        }

        .card-accent-warning .card-header {
            background-color: #ffc107;
        }

        .card-accent-danger .card-header {
            background-color: #dc3545;
        }

        .card-accent-info .card-header {
            background-color: #17a2b8;
        }

        .card-accent-secondary .card-header {
            background-color: #6c757d;
        }

        .date-display {
            font-size: 1.1em;
            margin-bottom: 20px;
            color: #555;
        }


        .dashboard-container {
            display: flex;

        }

        .main-content {
            flex-grow: 1;

            margin-left: 20px;

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
                <li class="nav-item"><a href="input_jenis_menu.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Jenis Menu</span></a></li>
                <li class="nav-item"><a href="input_menu.php" class="nav-link"><i class="fas fa-hamburger"></i><span>Menu Makanan</span></a></li>
                <li class="nav-item"><a href="input_booking.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Booking</span></a></li>
                <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Laporan Analisis Penjualan</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Admin</span>
                </div>
            </div>

            <section id="laporan-analisis-section">
                <div class="container">
                    <h2 class="section-title">Laporan Analisis Penjualan</h2>
                    <p class="date-display">Tanggal Hari Ini: <?= htmlspecialchars($tanggal_hari_ini) ?></p>

                    <div class="card-grid">
                        <div class="card card-primary">
                            <div class="card-header">Menu Terfavorit</div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($menu_favorit['nama_menu'] ?? 'N/A') ?> - <?= htmlspecialchars($menu_favorit['total_dipesan'] ?? 0) ?>x</p>
                            </div>
                        </div>

                        <div class="card card-accent-success">
                            <div class="card-header">Menu Termahal</div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($menu_termahal['nama_menu'] ?? 'N/A') ?> - Rp<?= number_format($menu_termahal['harga'] ?? 0, 0, ',', '.') ?></p>
                            </div>
                        </div>

                        <div class="card card-accent-warning">
                            <div class="card-header">Menu Termurah</div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($menu_termurah['nama_menu'] ?? 'N/A') ?> - Rp<?= number_format($menu_termurah['harga'] ?? 0, 0, ',', '.') ?></p>
                            </div>
                        </div>

                        <div class="card card-accent-danger">
                            <div class="card-header">Menu Paling Tidak Diminati</div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($menu_tidak_diminati['nama_menu'] ?? 'N/A') ?> - <?= htmlspecialchars($menu_tidak_diminati['total_dipesan'] ?? 0) ?>x</p>
                            </div>
                        </div>

                        <div class="card card-accent-info">
                            <div class="card-header">Jenis Menu Paling Banyak Dipesan</div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($jenis_menu_populer['nama_jenis'] ?? 'N/A') ?> - <?= htmlspecialchars($jenis_menu_populer['total_dipesan'] ?? 0) ?>x</p>
                            </div>
                        </div>

                        <div class="card card-accent-secondary">
                            <div class="card-header">Menu Paling Banyak Dipesan via Meja</div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($pesan_meja['nama_menu'] ?? 'Tidak ada data') ?> - <?= htmlspecialchars($pesan_meja['total'] ?? 0) ?>x</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        /* ========= NAVIGATION ========= */
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', e => {


                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
   
                link.classList.add('active');
            });
        });

        /* ========= INIT ========= */
        document.addEventListener('DOMContentLoaded', () => {

            const currentPath = window.location.pathname.split('/').pop();
            const activeLink = document.querySelector(`.nav-link[href="${currentPath}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            } else {
                // Fallback if no specific link matches, e.g., if it's the dashboard.php
                // Or you might want to specifically activate 'Dashboard' if no other link is active
                // For this report page, we explicitly set the 'Laporan' link to active in HTML.
            }

            // Update page title based on the active section (though for single page, it's static)
            const pageTitleElement = document.getElementById('page-title');
            if (pageTitleElement) {
                pageTitleElement.textContent = 'Laporan Analisis Penjualan';
            }
        });
    </script>
</body>

</html>
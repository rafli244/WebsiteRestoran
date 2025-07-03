<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Tetapkan zona waktu
include 'koneksi.php'; // Sertakan koneksi database

$tanggal_hari_ini = date('Y-m-d'); // Dapatkan tanggal hari ini
$message = null; // Untuk pesan error

// Fungsi helper untuk mengambil satu baris hasil dari query (tanpa prepared statement)
function getSingleResultDirect($koneksi, $sql)
{
    $result = mysqli_query($koneksi, $sql);
    if ($result) {
        return mysqli_fetch_assoc($result);
    } else {
        error_log("Query failed: " . mysqli_error($koneksi) . " SQL: " . $sql);
        return null;
    }
}

// Query untuk mendapatkan menu terfavorit
$menu_favorit = getSingleResultDirect(
    $koneksi,
    "SELECT m.nama_menu, SUM(dt.jumlah) AS total_dipesan
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     GROUP BY m.id_menu, m.nama_menu
     ORDER BY total_dipesan DESC LIMIT 1"
);

// Query untuk mendapatkan menu termahal
$menu_termahal = getSingleResultDirect(
    $koneksi,
    "SELECT nama_menu, harga FROM menu ORDER BY harga DESC LIMIT 1"
);

// Query untuk mendapatkan menu termurah
$menu_termurah = getSingleResultDirect(
    $koneksi,
    "SELECT nama_menu, harga FROM menu ORDER BY harga ASC LIMIT 1"
);

// Query untuk mendapatkan total pendapatan hari ini
$total_pendapatan_hari_ini = getSingleResultDirect(
    $koneksi,
    "SELECT SUM(total_keuntungan) AS total FROM transaksi WHERE DATE(tanggal) = '$tanggal_hari_ini'"
)['total'] ?? 0;

// Query untuk mendapatkan total transaksi hari ini
$total_transaksi_hari_ini = getSingleResultDirect(
    $koneksi,
    "SELECT COUNT(id_transaksi) AS total FROM transaksi WHERE DATE(tanggal) = '$tanggal_hari_ini'"
)['total'] ?? 0;

// Query untuk mendapatkan jenis menu paling banyak dipesan
$jenis_menu_populer = getSingleResultDirect(
    $koneksi,
    "SELECT jm.nama_jenis, SUM(dt.jumlah) AS total_dipesan
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     JOIN jenis_menu jm ON m.id_jenis = jm.id_jenis
     GROUP BY jm.id_jenis, jm.nama_jenis
     ORDER BY total_dipesan DESC LIMIT 1"
);

// Query untuk mendapatkan menu paling banyak dipesan via meja
$pesan_meja = getSingleResultDirect(
    $koneksi,
    "SELECT m.nama_menu, SUM(dbm.jumlah) AS total
     FROM detail_booking_menu dbm
     JOIN menu m ON dbm.id_menu = m.id_menu
     GROUP BY m.id_menu, m.nama_menu
     ORDER BY total DESC LIMIT 1"
);

mysqli_close($koneksi);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan – Double Box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index1.css">
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .card-report {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: center;
        }
        .card-report .card-header {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-text);
        }
        .card-report .card-body p {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary);
            margin: 0;
        }
        .card-accent-primary .card-header { color: var(--primary); }
        .card-accent-secondary .card-header { color: var(--secondary); }
        .card-accent-success .card-header { color: var(--success); }
        .card-accent-info .card-header { color: var(--info); }
        .card-accent-warning .card-header { color: var(--warning); }
        .card-accent-danger .card-header { color: var(--danger); }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include '_sidebar.php'; // Sertakan sidebar ?>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Laporan Analisis</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="reports-section">
                <?php if ($message): ?>
                    <p class="error-message"><?= $message ?></p>
                <?php endif; ?>

                <div class="report-grid">
                    <div class="card-report card-accent-primary">
                        <div class="card-header">Total Pendapatan Hari Ini (<?= date('d M Y') ?>)</div>
                        <div class="card-body">
                            <p>Rp <?= number_format($total_pendapatan_hari_ini, 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <div class="card-report card-accent-secondary">
                        <div class="card-header">Total Transaksi Hari Ini (<?= date('d M Y') ?>)</div>
                        <div class="card-body">
                            <p><?= number_format($total_transaksi_hari_ini, 0, ',', '.') ?> Transaksi</p>
                        </div>
                    </div>

                    <div class="card-report card-accent-success">
                        <div class="card-header">Menu Paling Banyak Dipesan</div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($menu_favorit['nama_menu'] ?? 'N/A') ?> - <?= number_format($menu_favorit['total_dipesan'] ?? 0, 0, ',', '.') ?>x</p>
                        </div>
                    </div>

                    <div class="card-report card-accent-info">
                        <div class="card-header">Menu Termahal</div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($menu_termahal['nama_menu'] ?? 'N/A') ?> - Rp <?= number_format($menu_termahal['harga'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <div class="card-report card-accent-warning">
                        <div class="card-header">Menu Termurah</div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($menu_termurah['nama_menu'] ?? 'N/A') ?> - Rp <?= number_format($menu_termurah['harga'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <div class="card-report card-accent-secondary">
                        <div class="card-header">Jenis Menu Paling Banyak Dipesan</div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($jenis_menu_populer['nama_jenis'] ?? 'N/A') ?> - <?= number_format($jenis_menu_populer['total_dipesan'] ?? 0, 0, ',', '.') ?>x</p>
                        </div>
                    </div>

                    <div class="card-report card-accent-secondary">
                        <div class="card-header">Menu Paling Banyak Dipesan via Meja</div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($pesan_meja['nama_menu'] ?? 'Tidak ada data') ?> - <?= number_format($pesan_meja['total'] ?? 0, 0, ',', '.') ?>x</p>
                        </div>
                    </div>
                </div>
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
<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan</title>
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
        .container input[type="datetime-local"],
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
                <li class="nav-item"><a href="pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Kasir</span></a></li>
                <li class="nav-item"><a href="input_jenis_menu.php" class="nav-link"><i class="fas fa-list"></i><span>Jenis Menu</span></a></li>
                <li class="nav-item"><a href="input_menu.php" class="nav-link"><i class="fas fa-hamburger"></i><span>Menu Makanan</span></a></li>
                <li class="nav-item"><a href="booking.php" class="nav-link active"><i class="fas fa-calendar-check"></i><span>Booking</span></a></li>
                <li class="nav-item"><a href="tampil.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Form Pemesanan</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Admin</span>
                </div>
            </div>

            <section id="booking-section">
                <div class="container">
                    <h2>Pesan Sekarang!</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="nama_pelanggan">Nama Pelanggan:</label>
                            <input type="text" id="nama_pelanggan" name="nama_pelanggan" placeholder="Nama pelanggan" required>
                        </div>

                        <div class="form-group">
                            <label for="jenis_booking">Jenis Pemesanan:</label>
                            <select id="jenis_booking" name="jenis_booking">
                                <option value="meja">Pesan Meja</option>
                                <option value="takeaway">Takeaway</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="waktu_booking">Waktu Pemesanan:</label>
                            <input type="datetime-local" id="waktu_booking" name="waktu_booking" required>
                        </div>

                        <div class="menu-selection-section">
                            <h3>Pilih Menu Favorit Anda:</h3>
                            <?php
                            $menu = mysqli_query($koneksi, "SELECT id_menu, nama_menu, harga FROM menu ORDER BY nama_menu ASC");

                            if ($menu && mysqli_num_rows($menu) > 0) {
                                while ($menu_item_db = mysqli_fetch_assoc($menu)) {
                                    $clean_menu_name = str_replace(' ', '_', strtolower($menu_item_db['nama_menu']));
                                    echo "<div class='menu-item'>";
                                    echo "<input type='checkbox' name='menu_items[{$menu_item_db['id_menu']}]' value='{$menu_item_db['nama_menu']}' data-price='{$menu_item_db['harga']}' id='menu_{$clean_menu_name}'>";
                                    echo "<label for='menu_{$clean_menu_name}'>{$menu_item_db['nama_menu']} - Rp " . number_format($menu_item_db['harga'], 0, ',', '.') . "</label>";
                                    echo "<input type='number' name='qty_{$clean_menu_name}' min='1' value='1' disabled>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p>Tidak ada menu tersedia saat ini.</p>";
                            }
                            ?>
                        </div>

                        <button type="submit">Konfirmasi Pemesanan</button>
                    </form>

                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
                        $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_booking']);
                        $waktu = mysqli_real_escape_string($koneksi, $_POST['waktu_booking']);

                        $menu_dipesan_array = [];
                        $total_harga = 0;

                        if (isset($_POST['menu_items']) && is_array($_POST['menu_items'])) {
                            foreach ($_POST['menu_items'] as $id_menu => $menu_name_val) {
                                $stmt_menu_detail = mysqli_prepare($koneksi, "SELECT nama_menu, harga FROM menu WHERE id_menu = ?");
                                mysqli_stmt_bind_param($stmt_menu_detail, "i", $id_menu);
                                mysqli_stmt_execute($stmt_menu_detail);
                                $result_menu_detail = mysqli_stmt_get_result($stmt_menu_detail);
                                $db_menu_item = mysqli_fetch_assoc($result_menu_detail);
                                mysqli_stmt_close($stmt_menu_detail);

                                if ($db_menu_item) {
                                    $actual_menu_name = $db_menu_item['nama_menu'];
                                    $actual_menu_price = $db_menu_item['harga'];

                                    // Get quantity using the sanitized name from DB
                                    $clean_menu_name_for_qty = str_replace(' ', '_', strtolower($actual_menu_name));
                                    $qty = isset($_POST['qty_' . $clean_menu_name_for_qty]) ? intval($_POST['qty_' . $clean_menu_name_for_qty]) : 1;
                                    if ($qty < 1) $qty = 1; // Ensure quantity is at least1

                                    $menu_dipesan_array[] = "{$actual_menu_name} (x{$qty})";
                                    $total_harga += ($actual_menu_price * $qty);
                                }
                            }
                        }
                        $menu_text = implode(", ", $menu_dipesan_array);

                        // Save to database (using mysqli_query for simplicity in this prototype)
                        // Ensure column `total_harga` exists in your `booking` table if you want to save it
                        $sql_insert_booking = "INSERT INTO booking (nama_pelanggan, jenis_booking, waktu_booking, menu_dipesan, total_harga) 
                                   VALUES ('$nama', '$jenis', '$waktu', '$menu_text', '$total_harga')";

                        if (mysqli_query($koneksi, $sql_insert_booking)) {
                            echo '<p class="success-message">Data booking has been successfully added. Total Harga: Rp ' . number_format($total_harga, 0, ',', '.') . '</p>';
                        } else {
                            echo '<p class="error-message">Error: ' . mysqli_error($koneksi) . '</p>';
                        }

                        // mysqli_close($koneksi); // Close connection if no more database operations are needed
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Enable quantity input when checkbox is checked
        document.querySelectorAll('.menu-selection-section input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Find the corresponding quantity input for this checkbox
                const qtyInput = this.nextElementSibling.nextElementSibling; // Skip label, then get input
                if (qtyInput && qtyInput.type === 'number') {
                    qtyInput.disabled = !this.checked;
                    if (!this.checked) {
                        qtyInput.value = 1; // Reset quantity if unchecked
                    }
                }
            });
        });

        // Ensure minimum date for datetime-local is current time in Pekanbaru
        // Note: This relies on client-side time, which can be inaccurate for strict booking
        // For accurate booking, server-side validation of time slots is crucial.
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0'); // Perbaikan di sini (tambahkan + bukan spasi)
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');

        // Contoh format tanggal dan waktu
        const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}`;
        console.log(formattedDateTime); // Output: "2023-08-15 14:30" (contoh)
    </script>
</body>

</html>
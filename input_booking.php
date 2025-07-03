<?php
session_start();
include 'koneksi.php';

$message = '';
$menus = [];

$result_menu = mysqli_query($koneksi, "SELECT id_menu, nama_menu, harga FROM menu ORDER BY nama_menu ASC");
if ($result_menu) {
    while ($row = mysqli_fetch_assoc($result_menu)) {
        $menus[] = $row;
    }
} else {
    error_log("Failed to load menus: " . mysqli_error($koneksi));
    $message = '<p class="error-message">Gagal memuat menu.</p>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan'] ?? '');
    $jenis_booking = mysqli_real_escape_string($koneksi, $_POST['jenis_booking'] ?? 'meja');
    $jumlah_orang = (int)($_POST['jumlah_orang'] ?? 0);
    $waktu_booking = mysqli_real_escape_string($koneksi, $_POST['waktu_booking'] ?? '');
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');

    $menu_items_booked = [];
    if (isset($_POST['menu_id']) && is_array($_POST['menu_id'])) {
        foreach ($_POST['menu_id'] as $key => $menu_id_val) {
            $qty_val = $_POST['menu_qty'][$key] ?? 0;
            if (!empty($menu_id_val) && $qty_val > 0) {
                $menu_items_booked[] = [
                    'id_menu' => (int)$menu_id_val,
                    'quantity' => (int)$qty_val
                ];
            }
        }
    }

    if (empty($nama_pelanggan) || $jumlah_orang <= 0 || empty($waktu_booking) || empty($menu_items_booked)) {
        $message = '<p class="error-message">Isi semua data: nama, jenis, jumlah orang, waktu, dan menu.</p>';
    } else {
        $menu_dipesan_text = '';
        foreach ($menu_items_booked as $item) {
            foreach ($menus as $menu) {
                if ($menu['id_menu'] == $item['id_menu']) {
                    $menu_dipesan_text .= $menu['nama_menu'] . ' x' . $item['quantity'] . '; ';
                }
            }
        }

        $sql = "INSERT INTO booking (nama_pelanggan, jenis_booking, waktu_booking, menu_dipesan, catatan, jumlah_orang)
                VALUES ('$nama_pelanggan', '$jenis_booking', '$waktu_booking', '$menu_dipesan_text', '$catatan', $jumlah_orang)";

        if (mysqli_query($koneksi, $sql)) {
            $message = '<p class="success-message">Booking berhasil disimpan!</p>';
            $_POST = array();
        } else {
            $message = '<p class="error-message">Gagal menyimpan booking: ' . mysqli_error($koneksi) . '</p>';
        }
    }
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan – Double Box</title>
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
        .container select,
        .container textarea {
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

        .menu-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .menu-row select {
            flex-grow: 1;
            width: auto;
            /* Override default width for flex */
        }

        .menu-row input[type="number"] {
            width: 80px;
            /* Specific width for quantity */
            flex-shrink: 0;
        }

        .menu-row button {
            background-color: var(--danger);
            flex-shrink: 0;
        }

        #menu-items-container {
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .add-menu-btn {
            background-color: var(--secondary);
            margin-top: 10px;
            padding: 8px 15px;
            font-size: 0.9em;
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
        <?php include '_sidebar.php'; // Sertakan sidebar 
        ?>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Form Pemesanan Meja</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="booking-section" class="container">
                <h2>Form Pemesanan</h2>
                <?php if ($message): ?>
                    <div class="message-container <?php echo strpos($message, 'success') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="input_booking.php" method="POST">
                    <label for="nama_pelanggan">Nama Pemesan:</label><br>
                    <input type="text" id="nama_pelanggan" name="nama_pelanggan" required value="<?= htmlspecialchars($_POST['nama_pelanggan'] ?? '') ?>"><br>

                    <label for="jumlah_orang">Jumlah Orang:</label><br>
                    <input type="number" id="jumlah_orang" name="jumlah_orang" min="1" required value="<?= htmlspecialchars($_POST['jumlah_orang'] ?? '') ?>"><br>

                    <label for="jenis_booking">Jenis Booking:</label><br>
                    <select name="jenis_booking" id="jenis_booking" required>
                        <option value="meja" <?= ($_POST['jenis_booking'] ?? '') === 'meja' ? 'selected' : '' ?>>Meja</option>
                        <option value="takeaway" <?= ($_POST['jenis_booking'] ?? '') === 'takeaway' ? 'selected' : '' ?>>Takeaway</option>
                    </select><br>


                    <label for="waktu_booking">Waktu Booking:</label><br>
                    <input type="datetime-local" id="waktu_booking" name="waktu_booking" required value="<?= htmlspecialchars($_POST['waktu_booking'] ?? date('Y-m-d\TH:i')) ?>"><br>

                    <label for="catatan">Catatan Tambahan:</label><br>
                    <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: Meja dekat jendela, alergi kacang, dll."><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea><br>

                    <h3>Pilih Menu yang Dipesan:</h3>
                    <div id="menu-items-container">
                        <?php if (!empty($_POST['menu_id'])): ?>
                            <?php foreach ($_POST['menu_id'] as $key => $mid): ?>
                                <div class="menu-row">
                                    <select name="menu_id[]" required>
                                        <option value="">Pilih Menu</option>
                                        <?php foreach ($menus as $menu): ?>
                                            <option value="<?= htmlspecialchars($menu['id_menu']) ?>" <?= ((string)$mid === (string)$menu['id_menu']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($menu['nama_menu']) ?> (Rp <?= number_format($menu['harga'], 0, ',', '.') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="menu_qty[]" value="<?= htmlspecialchars($_POST['menu_qty'][$key] ?? 1) ?>" min="1" required>
                                    <button type="button" onclick="removeMenuRow(this)">Hapus</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="menu-row">
                                <select name="menu_id[]" required>
                                    <option value="">Pilih Menu</option>
                                    <?php foreach ($menus as $menu): ?>
                                        <option value="<?= htmlspecialchars($menu['id_menu']) ?>">
                                            <?= htmlspecialchars($menu['nama_menu']) ?> (Rp <?= number_format($menu['harga'], 0, ',', '.') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="menu_qty[]" value="1" min="1" required>
                                <button type="button" onclick="removeMenuRow(this)">Hapus</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="add-menu-btn" onclick="addMenuRow()">Tambah Menu Lain</button><br><br>

                    <button type="submit">Buat Booking</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const now = new Date();
            const year = now.getFullYear();
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');

            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('waktu_booking').min = minDateTime;

            if (!document.getElementById('waktu_booking').value) {
                document.getElementById('waktu_booking').value = minDateTime;
            }

            const currentPath = window.location.pathname.split('/').pop();
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });

        function addMenuRow() {
            const container = document.getElementById('menu-items-container');
            const newRow = document.createElement('div');
            newRow.className = 'menu-row';
            newRow.innerHTML = `
                <select name="menu_id[]" required>
                    <option value="">Pilih Menu</option>
                    <?php foreach ($menus as $menu): ?>
                        <option value="<?= htmlspecialchars($menu['id_menu']) ?>">
                            <?= htmlspecialchars($menu['nama_menu']) ?> (Rp <?= number_format($menu['harga'], 0, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="menu_qty[]" value="1" min="1" required>
                <button type="button" onclick="removeMenuRow(this)">Hapus</button>
            `;
            container.appendChild(newRow);
        }

        function removeMenuRow(button) {
            const container = document.getElementById('menu-items-container');
            if (container.children.length > 1) { // Prevent removing the last row
                button.closest('.menu-row').remove();
            } else {
                alert('Setidaknya harus ada satu menu yang dipilih.');
            }
        }
    </script>
</body>

</html>
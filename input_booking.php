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

        .menu-selection-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .menu-item-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .menu-item-row select {
            flex: 2;
            width: auto;
        }

        .menu-item-row input[type="number"] {
            flex: 1;
            width: auto;
        }

        .menu-item-row button {
            flex: 0;
            width: auto;
            padding: 5px 10px;
            background-color: #dc3545;
            font-size: 12px;
        }

        .menu-item-row button:hover {
            background-color: #c82333;
        }

        .add-menu-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .add-menu-btn:hover {
            background-color: #218838;
        }

        .meja-input {
            display: none;
        }

        .meja-input.show {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .selected-menus {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .success-message {
            color: #28a745;
            font-weight: bold;
            margin-top: 15px;
        }

        .error-message {
            color: #dc3545;
            font-weight: bold;
            margin-top: 15px;
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
                            <select id="jenis_booking" name="jenis_booking" onchange="toggleMejaInput()">
                                <option value="meja">Pesan Meja</option>
                                <option value="takeaway">Takeaway</option>
                            </select>
                        </div>

                        <div class="form-group meja-input show" id="meja-input">
                            <label for="nomor_meja">Nomor Meja:</label>
                            <input type="number" id="nomor_meja" name="nomor_meja" placeholder="Masukkan nomor meja" min="1">
                        </div>

                        <div class="form-group">
                            <label for="waktu_booking">Waktu Pemesanan:</label>
                            <input type="datetime-local" id="waktu_booking" name="waktu_booking" required>
                        </div>

                        <div class="menu-selection-section">
                            <h3>Pilih Menu Favorit Anda:</h3>
                            <button type="button" class="add-menu-btn" onclick="addMenuRow()">+ Tambah Menu</button>
                            
                            <div id="menu-container">
                                <!-- Menu rows will be added here dynamically -->
                            </div>

                            <div class="selected-menus" id="selected-menus" style="display: none;">
                                <h4>Menu yang Dipilih:</h4>
                                <div id="menu-summary"></div>
                                <div id="total-price" style="font-weight: bold; margin-top: 10px;"></div>
                            </div>
                        </div>

                        <button type="submit">Konfirmasi Pemesanan</button>
                    </form>

                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
                        $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_booking']);
                        $waktu = mysqli_real_escape_string($koneksi, $_POST['waktu_booking']);
                        
                        // Handle nomor meja
                        $jenis_detail = $jenis;
                        if ($jenis == 'meja' && !empty($_POST['nomor_meja'])) {
                            $nomor_meja = intval($_POST['nomor_meja']);
                            $jenis_detail = "meja " . $nomor_meja;
                        }

                        $menu_dipesan_array = [];
                        $total_harga = 0;

                        // Process selected menus from hidden inputs
                        if (isset($_POST['selected_menus']) && !empty($_POST['selected_menus'])) {
                            $selected_menus = json_decode($_POST['selected_menus'], true);
                            
                            if (is_array($selected_menus)) {
                                foreach ($selected_menus as $menu_item) {
                                    if (isset($menu_item['id']) && isset($menu_item['qty'])) {
                                        $id_menu = intval($menu_item['id']);
                                        $qty = intval($menu_item['qty']);
                                        
                                        if ($id_menu > 0 && $qty > 0) {
                                            // Get menu details from database
                                            $stmt_menu = mysqli_prepare($koneksi, "SELECT nama_menu, harga FROM menu WHERE id_menu = ?");
                                            
                                            if ($stmt_menu) {
                                                mysqli_stmt_bind_param($stmt_menu, "i", $id_menu);
                                                mysqli_stmt_execute($stmt_menu);
                                                $result_menu = mysqli_stmt_get_result($stmt_menu);
                                                $menu_data = mysqli_fetch_assoc($result_menu);
                                                mysqli_stmt_close($stmt_menu);
                                                
                                                if ($menu_data) {
                                                    $menu_dipesan_array[] = "{$menu_data['nama_menu']} (x{$qty})";
                                                    $total_harga += ($menu_data['harga'] * $qty);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $menu_text = implode(", ", $menu_dipesan_array);

                        // Save to database
                        $sql_insert_booking = "INSERT INTO booking (nama_pelanggan, jenis_booking, waktu_booking, menu_dipesan, total_harga) 
                                               VALUES (?, ?, ?, ?, ?)";
                        
                        $stmt = mysqli_prepare($koneksi, $sql_insert_booking);
                        
                        if ($stmt === false) {
                            // Fallback to regular query if prepared statement fails
                            $nama_escaped = mysqli_real_escape_string($koneksi, $nama);
                            $jenis_detail_escaped = mysqli_real_escape_string($koneksi, $jenis_detail);
                            $waktu_escaped = mysqli_real_escape_string($koneksi, $waktu);
                            $menu_text_escaped = mysqli_real_escape_string($koneksi, $menu_text);
                            
                            $sql_fallback = "INSERT INTO booking (nama_pelanggan, jenis_booking, waktu_booking, menu_dipesan, total_harga) 
                                           VALUES ('$nama_escaped', '$jenis_detail_escaped', '$waktu_escaped', '$menu_text_escaped', $total_harga)";
                            
                            if (mysqli_query($koneksi, $sql_fallback)) {
                                echo '<p class="success-message">Data booking berhasil ditambahkan. Total Harga: Rp ' . number_format($total_harga, 0, ',', '.') . '</p>';
                            } else {
                                echo '<p class="error-message">Error: ' . mysqli_error($koneksi) . '</p>';
                            }
                        } else {
                            mysqli_stmt_bind_param($stmt, "ssssi", $nama, $jenis_detail, $waktu, $menu_text, $total_harga);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                echo '<p class="success-message">Data booking berhasil ditambahkan. Total Harga: Rp ' . number_format($total_harga, 0, ',', '.') . '</p>';
                            } else {
                                echo '<p class="error-message">Error executing statement: ' . mysqli_stmt_error($stmt) . '</p>';
                            }
                            
                            mysqli_stmt_close($stmt);
                        }
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Hidden input to store selected menus data -->
    <input type="hidden" name="selected_menus" id="selected_menus_input">

    <script>
        // Menu data from PHP
        const menuData = [
            <?php
            $menu_query = mysqli_query($koneksi, "SELECT id_menu, nama_menu, harga FROM menu ORDER BY nama_menu ASC");
            $menu_items = [];
            
            if ($menu_query && mysqli_num_rows($menu_query) > 0) {
                while ($menu_item = mysqli_fetch_assoc($menu_query)) {
                    $menu_items[] = "{id: {$menu_item['id_menu']}, name: '{$menu_item['nama_menu']}', price: {$menu_item['harga']}}";
                }
            }
            echo implode(',', $menu_items);
            ?>
        ];

        let selectedMenus = [];
        let menuRowCounter = 0;

        function toggleMejaInput() {
            const jenisBooking = document.getElementById('jenis_booking').value;
            const mejaInput = document.getElementById('meja-input');
            const nomorMejaField = document.getElementById('nomor_meja');
            
            if (jenisBooking === 'meja') {
                mejaInput.classList.add('show');
                nomorMejaField.required = true;
            } else {
                mejaInput.classList.remove('show');
                nomorMejaField.required = false;
                nomorMejaField.value = '';
            }
        }

        function addMenuRow() {
            const container = document.getElementById('menu-container');
            const rowId = 'menu-row-' + menuRowCounter++;
            
            const menuRow = document.createElement('div');
            menuRow.className = 'menu-item-row';
            menuRow.id = rowId;
            
            let menuOptions = '<option value="">-- Pilih Menu --</option>';
            menuData.forEach(menu => {
                menuOptions += `<option value="${menu.id}" data-price="${menu.price}">${menu.name} - Rp ${menu.price.toLocaleString('id-ID')}</option>`;
            });
            
            menuRow.innerHTML = `
                <select onchange="updateMenuSelection('${rowId}')">
                    ${menuOptions}
                </select>
                <input type="number" min="1" value="1" placeholder="Qty" onchange="updateMenuSelection('${rowId}')">
                <button type="button" onclick="removeMenuRow('${rowId}')">Hapus</button>
            `;
            
            container.appendChild(menuRow);
        }

        function removeMenuRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                updateSelectedMenus();
            }
        }

        function updateMenuSelection(rowId) {
            updateSelectedMenus();
        }

        function updateSelectedMenus() {
            selectedMenus = [];
            const menuRows = document.querySelectorAll('.menu-item-row');
            
            menuRows.forEach(row => {
                const select = row.querySelector('select');
                const qtyInput = row.querySelector('input[type="number"]');
                
                if (select.value && qtyInput.value) {
                    const selectedOption = select.options[select.selectedIndex];
                    selectedMenus.push({
                        id: select.value,
                        name: selectedOption.text.split(' - ')[0],
                        price: parseInt(selectedOption.dataset.price),
                        qty: parseInt(qtyInput.value)
                    });
                }
            });
            
            // Update hidden input
            document.getElementById('selected_menus_input').value = JSON.stringify(selectedMenus);
            
            // Update summary display
            updateMenuSummary();
        }

        function updateMenuSummary() {
            const summaryDiv = document.getElementById('menu-summary');
            const totalPriceDiv = document.getElementById('total-price');
            const selectedMenusDiv = document.getElementById('selected-menus');
            
            if (selectedMenus.length === 0) {
                selectedMenusDiv.style.display = 'none';
                return;
            }
            
            selectedMenusDiv.style.display = 'block';
            
            let summaryHTML = '';
            let totalPrice = 0;
            
            selectedMenus.forEach(menu => {
                const subtotal = menu.price * menu.qty;
                totalPrice += subtotal;
                summaryHTML += `<div>${menu.name} x${menu.qty} = Rp ${subtotal.toLocaleString('id-ID')}</div>`;
            });
            
            summaryDiv.innerHTML = summaryHTML;
            totalPriceDiv.innerHTML = `Total: Rp ${totalPrice.toLocaleString('id-ID')}`;
        }

        // Initialize with one menu row
        addMenuRow();

        // Set minimum datetime to current time
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        
        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('waktu_booking').min = minDateTime;
    </script>
</body>

</html>
<?php
session_start();
include 'koneksi.php'; // Pastikan file ini berisi $koneksi = mysqli_connect(...)

// Inisialisasi keranjang belanja jika belum ada
$_SESSION['keranjang'] = $_SESSION['keranjang'] ?? [];

$pesanStatus = ''; // Variabel untuk pesan status (sukses/gagal)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksiPermintaan = $_POST['action'] ?? '';
    $idItem = (int)($_POST['menu_id'] ?? $_POST['item_index'] ?? 0); // ID menu atau indeks item keranjang
    $jumlah = (int)($_POST['quantity'] ?? $_POST['new_quantity'] ?? 0); // Jumlah item atau jumlah baru

    if ($aksiPermintaan === 'add_to_cart') {
        if ($jumlah <= 0) {
            $pesanStatus = '<div class="error-message">Jumlah menu tidak valid.</div>';
        } else {
            // Ambil detail menu dari database menggunakan prepared statement
            $stmtMenu = mysqli_prepare($koneksi, "SELECT id_menu, nama_menu, harga FROM menu WHERE id_menu = ?");
            mysqli_stmt_bind_param($stmtMenu, 'i', $idItem);
            mysqli_stmt_execute($stmtMenu);
            $hasilMenu = mysqli_stmt_get_result($stmtMenu);
            $detailMenu = mysqli_fetch_assoc($hasilMenu);
            mysqli_stmt_close($stmtMenu);

            if ($detailMenu) {
                $itemDitemukanDiKeranjang = false;
                foreach ($_SESSION['keranjang'] as $kunci => &$itemKeranjang) { // Gunakan referensi untuk modifikasi langsung
                    if ($itemKeranjang['id_menu'] === $idItem) {
                        $itemKeranjang['quantity'] += $jumlah;
                        $itemKeranjang['subtotal'] = $itemKeranjang['quantity'] * $detailMenu['harga'];
                        $itemDitemukanDiKeranjang = true;
                        break;
                    }
                }
                unset($itemKeranjang); // Putuskan referensi setelah loop

                if (!$itemDitemukanDiKeranjang) {
                    $_SESSION['keranjang'][] = [
                        'id_menu' => $detailMenu['id_menu'],
                        'nama_menu' => $detailMenu['nama_menu'],
                        'price' => (float)$detailMenu['harga'],
                        'quantity' => $jumlah,
                        'subtotal' => $jumlah * (float)$detailMenu['harga']
                    ];
                }
                $pesanStatus = '<div class="success-message">Menu berhasil ditambahkan ke keranjang.</div>';
            } else {
                $pesanStatus = '<div class="error-message">Menu tidak ditemukan.</div>';
            }
        }
    } elseif (in_array($aksiPermintaan, ['remove_from_cart', 'update_cart_quantity'])) {
        // $idItem di sini adalah indeks array dari item di keranjang
        if (isset($_SESSION['keranjang'][$idItem])) {
            if ($aksiPermintaan === 'update_cart_quantity' && $jumlah > 0) {
                $_SESSION['keranjang'][$idItem]['quantity'] = $jumlah;
                $_SESSION['keranjang'][$idItem]['subtotal'] = $jumlah * $_SESSION['keranjang'][$idItem]['price'];
                $pesanStatus = '<div class="success-message">Jumlah item di keranjang berhasil diperbarui.</div>';
            } else { // Jika aksi hapus atau jumlah <= 0
                array_splice($_SESSION['keranjang'], $idItem, 1);
                $pesanStatus = '<div class="success-message">Item berhasil dihapus dari keranjang.</div>';
            }
        } else {
            $pesanStatus = '<div class="error-message">Item tidak ditemukan di keranjang.</div>';
        }
    } elseif (isset($_POST['checkout'])) {
        $totalPembayaran = array_reduce($_SESSION['keranjang'], fn($sum, $item) => $sum + $item['subtotal'], 0);

        if (empty($_SESSION['keranjang']) || $totalPembayaran <= 0) {
            $pesanStatus = '<div class="error-message">Keranjang belanja kosong atau total tidak valid.</div>';
        } else {
            mysqli_begin_transaction($koneksi); // Mulai transaksi database
            try {
                $tanggalTransaksi = date('Y-m-d H:i:s');
                // Query dasar: Insert ke tabel transaksi
                $stmtTransaksi = mysqli_prepare($koneksi, "INSERT INTO transaksi (tanggal, total_keuntungan) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmtTransaksi, 'sd', $tanggalTransaksi, $totalPembayaran);
                mysqli_stmt_execute($stmtTransaksi) or throw new Exception(mysqli_stmt_error($stmtTransaksi));
                $idTransaksiBaru = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmtTransaksi);

                // Query dasar: Insert ke detail transaksi dan update total_terjual menu
                $stmtDetail = mysqli_prepare($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_menu, jumlah, subtotal) VALUES (?, ?, ?, ?)");
                $stmtUpdateMenu = mysqli_prepare($koneksi, "UPDATE menu SET total_terjual = total_terjual + ? WHERE id_menu = ?");

                foreach ($_SESSION['keranjang'] as $itemKeranjang) {
                    mysqli_stmt_bind_param($stmtDetail, 'iiid', $idTransaksiBaru, $itemKeranjang['id_menu'], $itemKeranjang['quantity'], $itemKeranjang['subtotal']);
                    mysqli_stmt_execute($stmtDetail) or throw new Exception(mysqli_stmt_error($stmtDetail));

                    mysqli_stmt_bind_param($stmtUpdateMenu, 'ii', $itemKeranjang['quantity'], $itemKeranjang['id_menu']);
                    mysqli_stmt_execute($stmtUpdateMenu) or throw new Exception(mysqli_stmt_error($stmtUpdateMenu));
                }
                mysqli_stmt_close($stmtDetail);
                mysqli_stmt_close($stmtUpdateMenu);

                mysqli_commit($koneksi); // Komit transaksi jika semua berhasil
                $_SESSION['keranjang'] = []; // Kosongkan keranjang setelah transaksi sukses
                header('Location: Kasir.php?status=success'); // Redirect dengan status sukses
                exit();
            } catch (Exception $e) {
                mysqli_rollback($koneksi); // Rollback transaksi jika ada kesalahan
                error_log("Kesalahan Checkout: " . $e->getMessage()); // Catat error ke log server
                header('Location: Kasir.php?status=error&msg=' . urlencode('Terjadi kesalahan saat menyimpan transaksi.')); // Redirect dengan status error
                exit();
            }
        }
    }
    // Redirect umum untuk semua aksi POST agar tidak terjadi resubmission form
    header('Location: Kasir.php');
    exit();
}

// Menampilkan pesan status setelah redirect (jika ada)
if (isset($_GET['status'])) {
    $pesanDefaultError = 'Terjadi kesalahan tidak dikenal.';
    $pesanTampil = ($_GET['status'] === 'success') ? 'Transaksi berhasil disimpan!' : ('Gagal menyimpan transaksi: ' . htmlspecialchars($_GET['msg'] ?? $pesanDefaultError));
    $kelasPesan = ($_GET['status'] === 'success') ? 'success-message' : 'error-message';
    $pesanStatus = "<div class=\"$kelasPesan\">$pesanTampil</div>";
}

// Ambil data menu untuk ditampilkan (memanfaatkan indeks untuk performa)
$daftarMenu = [];
$hasilQueryMenu = mysqli_query($koneksi, "SELECT id_menu, nama_menu, harga, deskripsi, gambar, id_jenis FROM menu USE INDEX (idx_nama_menu) ORDER BY nama_menu ASC");
if ($hasilQueryMenu) {
    while ($barisMenu = mysqli_fetch_assoc($hasilQueryMenu)) {
        $daftarMenu[] = $barisMenu;
    }
} else {
    error_log("Gagal memuat menu: " . mysqli_error($koneksi)); // Catat error ke log
    $pesanStatus = '<div class="error-message">Gagal memuat daftar menu dari database.</div>';
}
mysqli_close($koneksi); // Tutup koneksi database

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir – Double Box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index1.css">
    <style>
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            padding: 20px;
            background-color: var(--light-bg);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .menu-item {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .menu-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
            background-color: #f8f9fa;
        }

        .menu-item .no-image {
            width: 100%;
            height: 120px;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 14px;
            text-align: center;
        }

        .menu-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .menu-info h4 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 1.1em;
            color: var(--dark-text);
        }

        .menu-info p {
            margin: 0;
            font-size: 0.9em;
            color: var(--gray);
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .menu-price {
            font-size: 1.1em;
            font-weight: 600;
            color: var(--primary);
            margin-top: 10px;
        }

        .add-to-cart-form {
            margin-top: 10px;
        }

        .add-quantity-input {
            width: 100%;
            padding: 5px;
            margin-bottom: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 8px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s ease;
        }

        .add-to-cart-btn:hover {
            background-color: var(--primary-dark);
        }

        .cart-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .cart-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--dark-text);
        }

        .cart-items {
            border-top: 1px solid #eee;
            margin-top: 15px;
            padding-top: 15px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }
        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-item-info h5 {
            margin: 0;
            color: var(--dark-text);
        }

        .cart-item-info span {
            font-size: 0.9em;
            color: var(--gray);
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-item-controls input[type="number"] {
            width: 60px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-align: center;
        }

        .cart-item-controls .remove-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #333;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .checkout-btn:hover {
            background-color: #21a69a;
        }

        .checkout-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .message-container {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
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

        .kasir-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .menu-list {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .cart-summary-panel {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            height: fit-content;
            position: sticky;
            top: 20px;
        }


    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include '_sidebar.php'; // Sertakan sidebar ?>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Sistem Kasir</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Kasir</span>
                </div>
            </div>

            <section id="kasir-section">
                <?php if ($message): ?>
                    <div class="message-container">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="kasir-layout">
                    <div class="menu-list">
                        <h2>Daftar Menu</h2>
                        <div class="menu-grid">
                            <?php if (empty($menus)): ?>
                                <p>Tidak ada menu yang tersedia.</p>
                            <?php else: ?>
                                <?php foreach ($menus as $menu): ?>
                                    <div class="menu-item" data-id="<?= htmlspecialchars($menu['id_menu']) ?>" data-name="<?= htmlspecialchars($menu['nama_menu']) ?>" data-price="<?= htmlspecialchars($menu['harga']) ?>">
                                        <?php if (!empty($menu['gambar']) && filter_var($menu['gambar'], FILTER_VALIDATE_URL)): ?>
                                            <img src="<?= htmlspecialchars($menu['gambar']) ?>" alt="<?= htmlspecialchars($menu['nama_menu']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="no-image" style="display: none;">
                                                <i class="fas fa-image"></i><br>Gambar tidak tersedia
                                            </div>
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i><br>Gambar tidak tersedia
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="menu-info">
                                            <h4><?= htmlspecialchars($menu['nama_menu']) ?></h4>
                                            <p><?= htmlspecialchars($menu['deskripsi'] ?? 'Tidak ada deskripsi.') ?></p>
                                            <div class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></div>
                                            <form action="Kasir.php" method="POST" class="add-to-cart-form">
                                                <input type="hidden" name="action" value="add_to_cart">
                                                <input type="hidden" name="menu_id" value="<?= htmlspecialchars($menu['id_menu']) ?>">
                                                <input type="number" name="quantity" value="1" min="1" class="add-quantity-input" onkeydown="return event.key !== 'Enter';">
                                                <button type="submit" class="add-to-cart-btn">Tambah <i class="fas fa-cart-plus"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="cart-summary-panel">
                        <h2>Keranjang Belanja</h2>
                        <div class="cart-items" id="cart-items-display">
                            <?php if (empty($_SESSION['cart'])): ?>
                                <p>Keranjang kosong.</p>
                            <?php else: ?>
                                <?php $total_cart_amount = 0; ?>
                                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                    <div class="cart-item">
                                        <div class="cart-item-info">
                                            <h5><?= htmlspecialchars($item['nama_menu']) ?></h5>
                                            <span>Rp <?= number_format($item['price'], 0, ',', '.') ?> x </span>
                                        </div>
                                        <div class="cart-item-controls">
                                            <form action="Kasir.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_cart_quantity">
                                                <input type="hidden" name="item_index" value="<?= $index ?>">
                                                <input type="number" name="new_quantity" value="<?= htmlspecialchars($item['quantity']) ?>" min="0" onchange="this.form.submit()">
                                            </form>
                                            <form action="Kasir.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="remove_from_cart">
                                                <input type="hidden" name="item_index" value="<?= $index ?>">
                                                <button type="submit" class="remove-btn"><i class="fas fa-times"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php $total_cart_amount += $item['subtotal']; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="cart-summary">
                            <span>Total:</span>
                            <span id="cart-total-display">Rp <?= number_format($total_cart_amount ?? 0, 0, ',', '.') ?></span>
                        </div>
                        <form action="Kasir.php" method="POST">
                            <input type="hidden" name="checkout" value="1">
                            <button type="submit" class="checkout-btn" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>Checkout</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const formatterIDR = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });
        const formatCurrency = n => formatterIDR.format(n);

        document.querySelectorAll('.add-quantity-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
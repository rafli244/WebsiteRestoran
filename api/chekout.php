<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../koneksi.php'; // Sesuaikan path jika berbeda

if (!isset($koneksi) || $koneksi->connect_errno) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to connect to database: ' . ($koneksi->connect_error ?? 'Koneksi objek tidak terinisialisasi.')
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input: ' . json_last_error_msg()
    ]);
    exit();
}

$cart_items = $input['cart_items'] ?? [];
$total_amount = $input['total_amount'] ?? 0;

if (empty($cart_items) || $total_amount <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Keranjang kosong atau total tidak valid.'
    ]);
    exit();
}

// Mulai transaksi database
$koneksi->begin_transaction();

try {
    // 1. Masukkan data ke tabel `transaksi`
    $tanggal_transaksi = date('Y-m-d');
    $stmt_transaksi = $koneksi->prepare("INSERT INTO transaksi (tanggal, total_keuntungan) VALUES (?, ?)");
    if (!$stmt_transaksi) {
        throw new Exception("Prepare statement for transaksi failed: " . $koneksi->error);
    }
    $stmt_transaksi->bind_param("sd", $tanggal_transaksi, $total_amount); // 's' for string (date), 'd' for double (decimal)
    $stmt_transaksi->execute();

    if ($stmt_transaksi->affected_rows === 0) {
        throw new Exception("Failed to insert transaction into 'transaksi' table.");
    }

    $id_transaksi_baru = $koneksi->insert_id; // Dapatkan ID transaksi yang baru dibuat
    $stmt_transaksi->close();

    // 2. Masukkan data ke tabel `detail_transaksi` dan update `total_terjual` di `menu`
    $stmt_detail = $koneksi->prepare("INSERT INTO detail_transaksi (id_transaksi, id_menu, jumlah, subtotal) VALUES (?, ?, ?, ?)");
    $stmt_update_menu = $koneksi->prepare("UPDATE menu SET total_terjual = total_terjual + ? WHERE id_menu = ?");

    if (!$stmt_detail || !$stmt_update_menu) {
        throw new Exception("Prepare statement for detail_transaksi or update_menu failed: " . $koneksi->error);
    }

    foreach ($cart_items as $item) {
        $id_menu = $item['id_menu'];
        $jumlah = $item['quantity'];
        $subtotal = $item['price'] * $item['quantity'];

        // Masukkan ke detail_transaksi
        $stmt_detail->bind_param("iiid", $id_transaksi_baru, $id_menu, $jumlah, $subtotal); // 'i' for int, 'd' for double
        $stmt_detail->execute();
        if ($stmt_detail->affected_rows === 0) {
            throw new Exception("Failed to insert detail transaction for menu ID " . $id_menu);
        }

        // Update total_terjual di tabel menu
        $stmt_update_menu->bind_param("ii", $jumlah, $id_menu);
        $stmt_update_menu->execute();
        if ($stmt_update_menu->affected_rows === 0) {
            error_log("Warning: Failed to update total_terjual for menu ID " . $id_menu);
        }
    }

    $stmt_detail->close();
    $stmt_update_menu->close();

    // Komit transaksi jika semua berhasil
    $koneksi->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaksi berhasil disimpan!',
        'id_transaksi' => $id_transaksi_baru
    ]);

} catch (Exception $e) {
    // Rollback transaksi jika terjadi kesalahan
    $koneksi->rollback();
    error_log("Checkout API Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
        'details' => $e->getMessage() // Hapus ini di produksi
    ]);
} finally {
    if (isset($koneksi) && $koneksi) {
        $koneksi->close();
    }
}
?>
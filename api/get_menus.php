<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../koneksi.php'; // Sesuaikan path jika berbeda

if (!isset($koneksi) || $koneksi->connect_errno) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to connect to database: ' . ($koneksi->connect_error ?? 'Koneksi objek tidak terinisialisasi.')
    ]);
    exit();
}

try {
    $query = "SELECT id_menu, nama_menu, harga FROM menu ORDER BY nama_menu ASC";
    $result = $koneksi->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $koneksi->error);
    }

    $menus = [];
    while ($row = $result->fetch_assoc()) {
        // Pastikan harga adalah float untuk konsistensi JavaScript
        $row['harga'] = (float) $row['harga'];
        $menus[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'menus' => $menus
    ]);

} catch (Exception $e) {
    error_log("API Error (get_menus): " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve menus.',
        'details' => $e->getMessage() // Hapus ini di produksi
    ]);
} finally {
    if (isset($koneksi) && $koneksi) {
        $koneksi->close();
    }
}
?>
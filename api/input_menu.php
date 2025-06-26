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

$nama_menu = $input['nama_menu'] ?? '';
$harga = $input['harga'] ?? 0;
$id_jenis = $input['id_jenis'] ?? '';

if (empty($nama_menu) || empty($harga) || empty($id_jenis)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Semua field harus diisi.'
    ]);
    exit();
}

if (!is_numeric($harga) || $harga < 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Harga harus berupa angka positif.'
    ]);
    exit();
}

try {
    $stmt = $koneksi->prepare("INSERT INTO menu (nama_menu, harga, id_jenis) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $nama_menu, $harga, $id_jenis);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Menu berhasil ditambahkan.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan menu.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($koneksi) && $koneksi) {
        $koneksi->close();
    }
}
?>
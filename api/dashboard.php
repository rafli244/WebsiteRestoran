<?php
// Pastikan tidak ada spasi kosong atau karakter lain sebelum <?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Izinkan semua origin. Sesuaikan jika Anda tahu domain frontend Anda.
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Tambahkan metode yang diperlukan
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Tambahkan header yang mungkin dikirim

// Tangani permintaan OPTIONS untuk preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Sesuaikan path ke file koneksi.php
// Berdasarkan struktur folder Anda, ini sudah benar karena koneksi.php ada di root proyek
require_once '../koneksi.php';

// Pastikan koneksi berhasil
if (!isset($koneksi) || $koneksi->connect_errno) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to connect to database: ' . ($koneksi->connect_error ?? 'Koneksi objek tidak terinisialisasi.')
    ]);
    exit();
}

try {
    $tanggal_hari_ini = date('Y-m-d');
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

    // --- 1. Total Pendapatan Hari Ini ---
    // MENGGUNAKAN KOLOM 'total_keuntungan' DARI TABEL 'transaksi'
    $query_pendapatan_hari_ini = $koneksi->prepare("SELECT SUM(total_keuntungan) AS total_penjualan FROM transaksi WHERE tanggal = ?");
    if (!$query_pendapatan_hari_ini) {
        throw new Exception("Prepare statement for pendapatan_hari_ini failed: " . $koneksi->error);
    }
    $query_pendapatan_hari_ini->bind_param("s", $tanggal_hari_ini);
    $query_pendapatan_hari_ini->execute();
    $result_pendapatan_hari_ini = $query_pendapatan_hari_ini->get_result();
    $pendapatan_hari_ini_data = $result_pendapatan_hari_ini->fetch_assoc();
    $total_pendapatan_hari_ini = $pendapatan_hari_ini_data['total_penjualan'] ?? 0;
    $query_pendapatan_hari_ini->close();

    // Total Pendapatan Kemarin
    $query_pendapatan_kemarin = $koneksi->prepare("SELECT SUM(total_keuntungan) AS total_penjualan_kemarin FROM transaksi WHERE tanggal = ?");
    if (!$query_pendapatan_kemarin) {
        throw new Exception("Prepare statement for pendapatan_kemarin failed: " . $koneksi->error);
    }
    $query_pendapatan_kemarin->bind_param("s", $tanggal_kemarin);
    $query_pendapatan_kemarin->execute();
    $result_pendapatan_kemarin = $query_pendapatan_kemarin->get_result();
    $pendapatan_kemarin_data = $result_pendapatan_kemarin->fetch_assoc();
    $total_pendapatan_kemarin = $pendapatan_kemarin_data['total_penjualan_kemarin'] ?? 0;
    $query_pendapatan_kemarin->close();

    $pendapatan_trend = "0%";
    if ($total_pendapatan_kemarin > 0) {
        $percentage_change = (($total_pendapatan_hari_ini - $total_pendapatan_kemarin) / $total_pendapatan_kemarin) * 100;
        $pendapatan_trend = number_format($percentage_change, 2) . "% " . ($percentage_change >= 0 ? "📈" : "📉");
    } elseif ($total_pendapatan_hari_ini > 0 && $total_pendapatan_kemarin == 0) {
        $pendapatan_trend = "+Inf% 📈 (dari 0)";
    } else {
        $pendapatan_trend = "Tetap";
    }

    // --- 2. Total Transaksi Hari Ini ---
    // Query ini sudah benar dengan skema database Anda
    $query_transaksi_hari_ini = $koneksi->prepare("SELECT COUNT(*) AS total_transactions FROM transaksi WHERE tanggal = ?");
    if (!$query_transaksi_hari_ini) {
        throw new Exception("Prepare statement for transaksi_hari_ini failed: " . $koneksi->error);
    }
    $query_transaksi_hari_ini->bind_param("s", $tanggal_hari_ini);
    $query_transaksi_hari_ini->execute();
    $result_transaksi_hari_ini = $query_transaksi_hari_ini->get_result();
    $transaksi_hari_ini_data = $result_transaksi_hari_ini->fetch_assoc();
    $total_transaksi_hari_ini = $transaksi_hari_ini_data['total_transactions'] ?? 0;
    $query_transaksi_hari_ini->close();

    // Total Transaksi Kemarin
    $query_transaksi_kemarin = $koneksi->prepare("SELECT COUNT(*) AS total_transactions_kemarin FROM transaksi WHERE tanggal = ?");
    if (!$query_transaksi_kemarin) {
        throw new Exception("Prepare statement for transaksi_kemarin failed: " . $koneksi->error);
    }
    $query_transaksi_kemarin->bind_param("s", $tanggal_kemarin);
    $query_transaksi_kemarin->execute();
    $result_transaksi_kemarin = $query_transaksi_kemarin->get_result();
    $transaksi_kemarin_data = $result_transaksi_kemarin->fetch_assoc();
    $total_transaksi_kemarin = $transaksi_kemarin_data['total_transactions_kemarin'] ?? 0;
    $query_transaksi_kemarin->close();

    $transaksi_trend = "0%";
    if ($total_transaksi_kemarin > 0) {
        $percentage_change = (($total_transaksi_hari_ini - $total_transaksi_kemarin) / $total_transaksi_kemarin) * 100;
        $transaksi_trend = number_format($percentage_change, 2) . "% " . ($percentage_change >= 0 ? "📈" : "📉");
    } elseif ($total_transaksi_hari_ini > 0 && $total_transaksi_kemarin == 0) {
        $transaksi_trend = "+Inf% 📈 (dari 0)";
    } else {
        $transaksi_trend = "Tetap";
    }

    // --- 3. Total Booking Hari Ini ---
    // Query ini sudah benar dengan skema database Anda (menggunakan waktu_booking)
    $query_booking_hari_ini = $koneksi->prepare("SELECT COUNT(*) AS total_bookings_today FROM booking WHERE DATE(waktu_booking) = ?");
    if (!$query_booking_hari_ini) {
        throw new Exception("Prepare statement for booking_hari_ini failed: " . $koneksi->error);
    }
    $query_booking_hari_ini->bind_param("s", $tanggal_hari_ini);
    $query_booking_hari_ini->execute();
    $result_booking_hari_ini = $query_booking_hari_ini->get_result();
    $booking_hari_ini_data = $result_booking_hari_ini->fetch_assoc();
    $total_booking_hari_ini = $booking_hari_ini_data['total_bookings_today'] ?? 0;
    $query_booking_hari_ini->close();

    // Total Booking Kemarin
    $query_booking_kemarin = $koneksi->prepare("SELECT COUNT(*) AS total_bookings_kemarin FROM booking WHERE DATE(waktu_booking) = ?");
    if (!$query_booking_kemarin) {
        throw new Exception("Prepare statement for booking_kemarin failed: " . $koneksi->error);
    }
    $query_booking_kemarin->bind_param("s", $tanggal_kemarin);
    $query_booking_kemarin->execute();
    $result_booking_kemarin = $query_booking_kemarin->get_result();
    $booking_kemarin_data = $result_booking_kemarin->fetch_assoc();
    $total_booking_kemarin = $booking_kemarin_data['total_bookings_kemarin'] ?? 0;
    $query_booking_kemarin->close();

    $booking_trend = "0%";
    if ($total_booking_kemarin > 0) {
        $percentage_change = (($total_booking_hari_ini - $total_booking_kemarin) / $total_booking_kemarin) * 100;
        $booking_trend = number_format($percentage_change, 2) . "% " . ($percentage_change >= 0 ? "📈" : "📉");
    } elseif ($total_booking_hari_ini > 0 && $total_booking_kemarin == 0) {
        $booking_trend = "+Inf% 📈 (dari 0)";
    } else {
        $booking_trend = "Tetap";
    }

    echo json_encode([
        'status' => 'success',
        'total_pendapatan' => (float)$total_pendapatan_hari_ini,
        'pendapatan_trend' => $pendapatan_trend,
        'total_transaksi' => (int)$total_transaksi_hari_ini,
        'transaksi_trend' => $transaksi_trend,
        'total_booking' => (int)$total_booking_hari_ini,
        'booking_trend' => $booking_trend,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Log error secara detail di server-side untuk debugging
    error_log("API Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal server error occurred.',
        // Hanya tampilkan detail error di lingkungan pengembangan, bukan produksi
        'details' => $e->getMessage()
    ]);
} finally {
    if (isset($koneksi) && $koneksi) {
        $koneksi->close();
    }
}
?>
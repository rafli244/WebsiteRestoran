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
    $today = date('Y-m-d');
    $seven_days_ago = date('Y-m-d', strtotime('-6 days')); // Ambil 7 hari termasuk hari ini

    $query = "
        SELECT
            DATE(tanggal) AS transaction_date,
            SUM(total_keuntungan) AS daily_revenue,
            COUNT(id_transaksi) AS daily_transactions
        FROM
            transaksi
        WHERE
            tanggal BETWEEN ? AND ?
        GROUP BY
            transaction_date
        ORDER BY
            transaction_date ASC
    ";

    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $koneksi->error);
    }
    $stmt->bind_param("ss", $seven_days_ago, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Query failed: " . $koneksi->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['transaction_date']] = [
            'revenue' => (float)$row['daily_revenue'],
            'transactions' => (int)$row['daily_transactions']
        ];
    }
    $stmt->close();

    // Siapkan data untuk Chart.js (pastikan semua 7 hari ada, bahkan jika tidak ada transaksi)
    $labels = [];
    $revenues = [];
    $transactions = [];

    for ($i = 6; $i >= 0; $i--) { // Loop 7 hari ke belakang dari hari ini
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($date)); // Format tanggal untuk label grafik (contoh: 19 Jun)

        $revenues[] = $data[$date]['revenue'] ?? 0;
        $transactions[] = $data[$date]['transactions'] ?? 0;
    }

    echo json_encode([
        'status' => 'success',
        'labels' => $labels,
        'datasets' => [
            'revenue' => $revenues,
            'transactions' => $transactions
        ]
    ]);

} catch (Exception $e) {
    error_log("Chart API Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve chart data.',
        'details' => $e->getMessage() // Hapus ini di produksi
    ]);
} finally {
    if (isset($koneksi) && $koneksi) {
        $koneksi->close();
    }
}
?>
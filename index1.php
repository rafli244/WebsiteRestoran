<?php
session_start(); // Pastikan sesi dimulai
include 'koneksi.php'; // Sertakan koneksi database

// Inisialisasi variabel default untuk dashboard
$total_pendapatan = 0;
$pendapatan_trend = '0%';
$total_transaksi = 0;
$transaksi_trend = '0%';
$total_booking = 0;
$booking_trend = '0%';
$db_error_message = null; // Variabel untuk pesan error database

// Data untuk chart
$chart_labels = [];
$chart_revenues = [];
$chart_transactions = [];

// Fungsi helper untuk menghitung trend (tetap relevan)
function calculateTrend($today, $yesterday) {
    if ($yesterday > 0) {
        $change = (($today - $yesterday) / $yesterday) * 100;
        return round($change, 2) . '% ' . ($change >= 0 ? '📈' : '📉');
    } elseif ($today > 0) {
        return '+∞% 📈';
    }
    return '0%';
}

// Ambil data dashboard dan chart
try {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Ambil data dashboard 
    $result = mysqli_query($koneksi, "SELECT SUM(total_keuntungan) AS total FROM transaksi WHERE DATE(tanggal) = '$today'");
    $today_revenue = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    $result = mysqli_query($koneksi, "SELECT SUM(total_keuntungan) AS total FROM transaksi WHERE DATE(tanggal) = '$yesterday'");
    $yesterday_revenue = mysqli_fetch_assoc($result)['total'] ?? 0;
    $pendapatan_trend = calculateTrend($today_revenue, $yesterday_revenue);
    $total_pendapatan = (float)$today_revenue;

    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi WHERE DATE(tanggal) = '$today'");
    $today_transactions = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi WHERE DATE(tanggal) = '$yesterday'");
    $yesterday_transactions = mysqli_fetch_assoc($result)['total'] ?? 0;
    $transaksi_trend = calculateTrend($today_transactions, $yesterday_transactions);
    $total_transaksi = (int)$today_transactions;

    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM booking WHERE DATE(waktu_booking) = '$today'");
    $today_bookings = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM booking WHERE DATE(waktu_booking) = '$yesterday'");
    $yesterday_bookings = mysqli_fetch_assoc($result)['total'] ?? 0;
    $booking_trend = calculateTrend($today_bookings, $yesterday_bookings);
    $total_booking = (int)$today_bookings;

    // Ambil data untuk Chart (7 hari terakhir)
    $seven_days_ago = date('Y-m-d', strtotime('-6 days'));
    $query_chart = "SELECT DATE(tanggal) AS transaction_date, 
                    SUM(total_keuntungan) AS daily_revenue, 
                    COUNT(id_transaksi) AS daily_transactions
                    FROM transaksi 
                    WHERE DATE(tanggal) BETWEEN '$seven_days_ago' AND '$today'
                    GROUP BY transaction_date ORDER BY transaction_date ASC";
    $result_chart = mysqli_query($koneksi, $query_chart);

    $chart_data_raw = [];
    if ($result_chart) {
        while ($row_chart = mysqli_fetch_assoc($result_chart)) {
            $chart_data_raw[$row_chart['transaction_date']] = [
                'revenue' => (float)$row_chart['daily_revenue'],
                'transactions' => (int)$row_chart['daily_transactions']
            ];
        }
    }

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('d M', strtotime($date));
        $chart_revenues[] = $chart_data_raw[$date]['revenue'] ?? 0;
        $chart_transactions[] = $chart_data_raw[$date]['transactions'] ?? 0;
    }

} catch (Exception $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    $db_error_message = 'Terjadi kesalahan saat memuat data: ' . $e->getMessage();
} finally {
    // Tutup koneksi setelah semua data diambil di sini
    mysqli_close($koneksi);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin – Double Box</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/index1.css">
  <style>
    /* Styling for error messages */
    .error-message {
      color: var(--danger);
      text-align: center;
      margin-top: 20px;
      font-weight: bold;
    }
    .loading {
      display: none; /* Hide loading placeholder as data is direct */
    }
    
  </style>
</head>

<body>
  <div class="dashboard-container">
    <?php include '_sidebar.php'; // Sertakan sidebar ?>

    <main class="main-content">
      <div class="header">
        <h1 class="page-title" id="page-title">Dashboard</h1>
        <div class="user-profile">
          <div class="user-avatar">A</div><span>Kasir</span>
        </div>
      </div>

      <section id="dashboard-section">
        <?php if (isset($db_error_message)): ?>
            <p class="error-message"><?php echo $db_error_message; ?></p>
        <?php endif; ?>

        <div class="dashboard-cards">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Total Pendapatan</h3>
              <div class="card-icon primary"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="card-value" id="total-pendapatan"><?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
            <div class="card-footer" id="pendapatan-trend"><?php echo $pendapatan_trend; ?></div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Total Transaksi</h3>
              <div class="card-icon secondary"><i class="fas fa-shopping-cart"></i></div>
            </div>
            <div class="card-value" id="total-transaksi"><?php echo number_format($total_transaksi, 0, ',', '.'); ?></div>
            <div class="card-footer" id="transaksi-trend"><?php echo $transaksi_trend; ?></div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Booking Hari Ini</h3>
              <div class="card-icon success"><i class="fas fa-calendar-day"></i></div>
            </div>
            <div class="card-value" id="total-booking"><span class="loading"></span><?php echo number_format($total_booking, 0, ',', '.'); ?></div>
            <div class="card-footer" id="booking-trend"><?php echo $booking_trend; ?></div>
          </div>
        </div>

        <h2 class="section-title"><i class="fas fa-bolt"></i><span>Quick Actions</span></h2>
        <div class="quick-actions">
          <a href="input_menu.php" class="action-button">
            <div class="action-icon"><i class="fas fa-plus"></i></div><span class="action-text">Tambah Menu</span>
          </a>
          <a href="input_booking.php" class="action-button">
            <div class="action-icon"><i class="fas fa-calendar-plus"></i></div><span class="action-text">Tambah Booking</span>
          </a>
          <a href="kasir.php" class="action-button" id="buat-transaksi-btn">
            <div class="action-icon"><i class="fas fa-cash-register"></i></div><span class="action-text">Buat Transaksi</span>
          </a>
          <a href="tampil.php" class="action-button">
            <div class="action-icon"><i class="fas fa-file-alt"></i></div><span class="action-text">Lihat Laporan</span>
          </a>
        </div>

        <h2 class="section-title"><i class="fas fa-chart-line"></i><span>Analisis Harian</span></h2>
        <div class="card chart-container">
          <h3 class="card-title" style="margin-bottom: 1rem;">Pendapatan & Transaksi 7 Hari Terakhir</h3>
          <canvas id="dailyAnalyticsChart"></canvas>
          <p id="chart-status" style="text-align: center; color: var(--gray); margin-top: 1rem;">
            <?php if (isset($db_error_message)): echo 'Gagal memuat grafik: ' . $db_error_message; else: echo ''; endif; ?>
          </p>
        </div>
      </section>
    </main>
  </div>

 <script>
  /* ========= HELPER ========= */
  const formatterIDR = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  });
  const formatCurrency = n => formatterIDR.format(n);
  const formatNumber = n => n.toLocaleString('id-ID');

  /* ========= CHART ========= */
  let dailyAnalyticsChart;

  document.addEventListener('DOMContentLoaded', () => {
    const chartLabels = [<?php echo "'" . implode("','", $chart_labels) . "'"; ?>];
    const chartRevenues = [<?php echo implode(',', $chart_revenues); ?>];
    const chartTransactions = [<?php echo implode(',', $chart_transactions); ?>];
    const chartStatus = document.getElementById('chart-status');

    if (chartLabels.length > 0) {
      const ctx = document.getElementById('dailyAnalyticsChart').getContext('2d');

      if (dailyAnalyticsChart) {
        dailyAnalyticsChart.destroy();
      }

      dailyAnalyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Pendapatan Harian',
            data: chartRevenues,
            borderColor: '#7E57C2',
            backgroundColor: 'rgba(126, 87, 194, 0.2)',
            fill: true,
            tension: 0.3
          }, {
            label: 'Jumlah Transaksi',
            data: chartTransactions,
            borderColor: '#26A69A',
            backgroundColor: 'rgba(38, 166, 154, 0.2)',
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Jumlah (Rp / Transaksi)'
              },
              ticks: {
                callback: function(value) {
                  return formatCurrency(value);
                }
              }
            },
            x: {
              title: {
                display: true,
                text: 'Tanggal'
              }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) label += ': ';
                  if (context.dataset.label === 'Pendapatan Harian') {
                    label += formatCurrency(context.raw);
                  } else {
                    label += formatNumber(context.raw);
                  }
                  return label;
                }
              }
            }
          }
        }
      });

      chartStatus.textContent = '';
    } else {
      chartStatus.textContent = 'Tidak ada data grafik yang tersedia.';
      chartStatus.style.color = 'gray';
    }
  });
</script>

</body>

</html>
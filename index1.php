<?php
// dashboard.php
include 'koneksi.php'; // Sertakan koneksi database

// Tidak ada kode PHP yang menghasilkan output JSON di sini lagi.
// Ini hanya file HTML/PHP untuk tampilan.
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
    /* Tambahkan style CSS yang diperlukan disini */
    .dashboard-cards .card {
      margin-bottom: 20px;
    }

    .quick-actions a {
      margin-right: 10px;
    }

    .chart-container {
      position: relative;
    }

    .chart-container #chart-status {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
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
        <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-cash-register"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a href="Kasir.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Kasir</span></a></li>
        <li class="nav-item"><a href="input_jenis_menu.php" class="nav-link"><i class="fas fa-list"></i><span>Jenis Menu</span></a></li>
        <li class="nav-item"><a href="input_menu.php" class="nav-link"><i class="fas fa-hamburger"></i><span>Menu Makanan</span></a></li>
        <li class="nav-item"><a href="input_booking.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Booking</span></a></li>
        <li class="nav-item"><a href="tampil.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
      </ul>
    </aside>

    <main class="main-content">
      <div class="header">
        <h1 class="page-title" id="page-title">Dashboard Admin</h1>
        <div class="user-profile">
          <div class="user-avatar">A</div><span>Admin</span>
        </div>
      </div>

      <section id="dashboard-section">
        <div class="dashboard-cards">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Total Pendapatan</h3>
              <div class="card-icon primary"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="card-value" id="total-pendapatan"><span class="loading"></span></div>
            <div class="card-footer" id="pendapatan-trend">Memuat data...</div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Total Transaksi</h3>
              <div class="card-icon secondary"><i class="fas fa-shopping-cart"></i></div>
            </div>
            <div class="card-value" id="total-transaksi"><span class="loading"></span></div>
            <div class="card-footer" id="transaksi-trend">Memuat data...</div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Booking Hari Ini</h3>
              <div class="card-icon success"><i class="fas fa-calendar-day"></i></div>
            </div>
            <div class="card-value" id="total-booking"><span class="loading"></span></div>
            <div class="card-footer" id="booking-trend">Memuat data...</div>
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
          <a href="pos.php" class="action-button" id="buat-transaksi-btn">
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
          <p id="chart-status" style="text-align: center; color: var(--gray); margin-top: 1rem;">Memuat grafik...</p>
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

    /* ========= DASHBOARD DATA ========= */
    let dashboardInterval;

    async function loadDashboardData() {
      console.log("Memulai pemuatan data dashboard...");
      try {
        const response = await fetch('api/dashboard.php');

        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`HTTP error! Status: ${response.status}. Response: ${errorText}`);
        }

        const data = await response.json();
        console.log("Data diterima dari API:", data);

        if (data.status === 'error') {
          const errorMessage = data.message + (data.details ? ' Details: ' + data.details : '');
          throw new Error(`Server Error: ${errorMessage}`);
        }

        document.getElementById('total-pendapatan').textContent = formatCurrency(Number(data.total_pendapatan || 0));
        document.getElementById('pendapatan-trend').textContent = data.pendapatan_trend || '0%';
        document.getElementById('total-transaksi').textContent = formatNumber(Number(data.total_transaksi || 0));
        document.getElementById('transaksi-trend').textContent = data.transaksi_trend || '0%';
        document.getElementById('total-booking').textContent = formatNumber(Number(data.total_booking || 0));
        document.getElementById('booking-trend').textContent = data.booking_trend || '0%';

      } catch (e) {
        console.error("Gagal memuat data dashboard:", e);
        ['total-pendapatan', 'total-transaksi', 'total-booking'].forEach(id => {
          const el = document.getElementById(id);
          if (el) el.textContent = 'Error';
        });
        ['pendapatan-trend', 'transaksi-trend', 'booking-trend'].forEach(id => {
          const el = document.getElementById(id);
          if (el) el.textContent = 'Gagal memuat';
        });
      }
    }

    function startDashboardAutoRefresh() {
      loadDashboardData();
      if (dashboardInterval) {
        clearInterval(dashboardInterval);
      }
      dashboardInterval = setInterval(loadDashboardData, 30000);
    }

    function stopDashboardAutoRefresh() {
      clearInterval(dashboardInterval);
    }

    /* ========= NAVIGATION ========= */
    document.querySelectorAll('.nav-link[data-target]').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const target = link.dataset.target;
        if (!target) return;

        document.querySelectorAll('main section').forEach(sec => sec.style.display = 'none');
        document.getElementById(target).style.display = 'block';
        document.getElementById('page-title').textContent = target === 'dashboard-section' ? 'Dashboard Admin' : 'Sistem Kasir';
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        if (target === 'dashboard-section') {
          startDashboardAutoRefresh();
        } else {
          stopDashboardAutoRefresh();
        }
      });
    });

    /* ========= INIT ========= */
    document.addEventListener('DOMContentLoaded', () => {
      startDashboardAutoRefresh();
    });

    /* ========= CHART ========= */
    let dailyAnalyticsChart;

    async function loadDailyAnalyticsChart() {
      const chartStatus = document.getElementById('chart-status');
      chartStatus.textContent = 'Memuat grafik...';
      chartStatus.style.color = 'var(--gray)';

      try {
        const response = await fetch('api/chart_data.php');
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        console.log("Data grafik diterima:", data);

        if (data.status === 'success') {
          const ctx = document.getElementById('dailyAnalyticsChart').getContext('2d');

          const dates = data.labels;
          const revenues = data.datasets.revenue;
          const transactions = data.datasets.transactions;

          if (dailyAnalyticsChart) {
            dailyAnalyticsChart.destroy();
          }

          dailyAnalyticsChart = new Chart(ctx, {
            type: 'line',
            data: {
              labels: dates,
              datasets: [{
                label: 'Pendapatan Harian',
                data: revenues,
                borderColor: 'var(--primary)',
                backgroundColor: 'rgba(126, 87, 194, 0.2)',
                fill: true,
                tension: 0.3
              }, {
                label: 'Jumlah Transaksi',
                data: transactions,
                borderColor: 'var(--secondary)',
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
                      if (label) {
                        label += ': ';
                      }
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
          chartStatus.textContent = 'Gagal memuat data grafik: ' + data.message;
          chartStatus.style.color = 'var(--danger)';
          console.error('Failed to load chart data:', data.message);
        }
      } catch (error) {
        chartStatus.textContent = 'Error koneksi saat memuat grafik: ' + error.message;
        chartStatus.style.color = 'var(--danger)';
        console.error('Error fetching chart data:', error);
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadDailyAnalyticsChart();
    });
  </script>
</body>

</html>
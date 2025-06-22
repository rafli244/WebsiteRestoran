<?php
// pos.php
include 'koneksi.php'; // Pastikan koneksi database sudah diatur di file ini

// Tidak ada kode PHP yang menghasilkan output JSON di sini lagi.
// Ini hanya file HTML/PHP untuk tampilan.
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir – Double Box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/index1.css">
    <style>
        /* Tambahkan style CSS yang diperlukan disini */
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

        .container input[type="text"] {
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
                <li class="nav-item"><a href="index1.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-cash-register"></i><span>Kasir</span></a></li>
                <li class="nav-item"><a href="input_jenis_menu.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Jenis Menu</span></a></li>
                <li class="nav-item"><a href="input_menu.php" class="nav-link"><i class="fas fa-hamburger"></i><span>Menu Makanan</span></a></li>
                <li class="nav-item"><a href="input_booking.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Booking</span></a></li>
                <li class="nav-item"><a href="tampil.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1 class="page-title" id="page-title">Sistem Kasir</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div><span>Admin</span>
                </div>
            </div>

            <section id="pos-section">
                <h2 class="section-title"><i class="fas fa-cash-register"></i><span>Sistem Kasir</span></h2>
                <div class="pos-container">
                    <div class="pos-products">
                        <h3 class="section-title" style="margin-top:0;"><i class="fas fa-utensils"></i>Daftar Menu</h3>
                        <div id="pos-products-list">
                            <p id="loading-menus">Memuat menu...</p>
                        </div>
                    </div>
                    <div class="pos-cart">
                        <h3 class="section-title" style="margin-top:0;"><i class="fas fa-shopping-cart"></i>Keranjang</h3>
                        <div class="cart-items" id="cart-items"></div>
                        <div class="cart-total" id="cart-total">Total: Rp 0</div>
                        <button class="checkout-btn" id="checkout-btn"><i class="fas fa-money-bill-wave"></i> Checkout</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // JavaScript untuk sistem kasir
        async function loadMenusFromDatabase() {
            const loadingMenusElement = document.getElementById('loading-menus');
            if (loadingMenusElement) loadingMenusElement.style.display = 'block';

            try {
                const response = await fetch('api/get_menus.php');
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                const data = await response.json();
                if (data.status === 'success') {
                    const container = document.getElementById('pos-products-list');
                    container.innerHTML = '';
                    data.menus.forEach(menu => {
                        const div = document.createElement('div');
                        div.className = 'pos-product-item';
                        div.innerHTML = `
              <div>
                  <span class="pos-product-name">${menu.nama_menu}</span><br>
                  <span class="pos-product-price">Rp ${menu.harga}</span>
              </div>
              <button class="pos-add-btn" data-id="${menu.id_menu}" data-name="${menu.nama_menu}" data-price="${menu.harga}">
                  <i class="fas fa-plus"></i> Tambah
              </button>`;
                        div.querySelector('button').addEventListener('click', () => {
                            addToCart({
                                id_menu: menu.id_menu,
                                name: menu.nama_menu,
                                price: parseFloat(menu.harga)
                            });
                        });
                        container.appendChild(div);
                    });
                } else {
                    document.getElementById('pos-products-list').innerHTML = '<p>Gagal memuat menu: ' + data.message + '</p>';
                }
            } catch (error) {
                document.getElementById('pos-products-list').innerHTML = '<p>Error koneksi: ' + error.message + '</p>';
            } finally {
                if (loadingMenusElement) loadingMenusElement.style.display = 'none';
            }
        }

        let cart = [];

        function addToCart(product) {
            const existingItem = cart.find(item => item.id_menu === product.id_menu);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    ...product,
                    quantity: 1
                });
            }
            renderCart();
        }

        function renderCart() {
            const itemsDiv = document.getElementById('cart-items');
            itemsDiv.innerHTML = '';
            let total = 0;
            if (cart.length === 0) {
                itemsDiv.innerHTML = '<p style="text-align: center; color: var(--gray);">Keranjang kosong.</p>';
            }
            cart.forEach((item, i) => {
                total += item.price * item.quantity;
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.innerHTML = `
          <span>${item.name} (x${item.quantity})</span>
          <span>Rp ${(item.price * item.quantity).toFixed(0)}</span>
        `;
                itemsDiv.appendChild(div);
            });
            document.getElementById('cart-total').textContent = 'Total: Rp ' + total.toFixed(0);
        }

        document.getElementById('checkout-btn').addEventListener('click', async () => {
            if (cart.length === 0) {
                alert('Keranjang kosong! Silakan tambahkan menu terlebih dahulu.');
                return;
            }

            const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const confirmation = confirm(`Konfirmasi Checkout?\nTotal: Rp ${totalAmount.toFixed(0)}\nJumlah item: ${cart.length}`);
            if (!confirmation) return;

            try {
                const response = await fetch('api/checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_items: cart,
                        total_amount: totalAmount
                    })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    alert('Transaksi berhasil disimpan ke database!');
                    cart = []; // Kosongkan keranjang
                    renderCart(); // Perbarui tampilan keranjang
                } else {
                    alert('Gagal menyimpan transaksi: ' + (data.message || 'Unknown error'));
                    console.error('Checkout error:', data);
                }
            } catch (error) {
                alert('Terjadi kesalahan jaringan saat checkout. Silakan coba lagi.');
                console.error('Network error during checkout:', error);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            loadMenusFromDatabase();
        });
    </script>
</body>

</html>
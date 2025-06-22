<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>
    <form method="POST">
    <select name="id_transaksi">
        <?php
        include 'koneksi.php';
        $trx = mysqli_query($koneksi, "SELECT * FROM transaksi");
        while ($row = mysqli_fetch_assoc($trx)) {
            echo "<option value='{$row['id_transaksi']}'>ID: {$row['id_transaksi']} - {$row['tanggal']}</option>";
        }
        ?>
    </select>
    <select name="id_menu">
        <?php
        $menu = mysqli_query($koneksi, "SELECT * FROM menu");
        while ($row = mysqli_fetch_assoc($menu)) {
            echo "<option value='{$row['id_menu']}'>'{$row['nama_menu']}</option>";
        }
        ?>

    </select>
    <input type="number" name="jumlah" placeholder="Jumlah" required>
    <input type="number" step="0.01" name="subtotal" placeholder="Subtotal" required>
    <button type="submit">Simpan</button>
</form>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id_transaksi = $_POST['id_transaksi'];
  $id_menu = $_POST['id_menu'];
  $jumlah = $_POST['jumlah'];
  $subtotal = $_POST['subtotal'];
  mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_menu, jumlah, subtotal) 
                          VALUES ('$id_transaksi', '$id_menu', '$jumlah', '$subtotal')");
  echo "Data detail transaksi berhasil ditambahkan.";
}
?>
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
    <input type="date" name="tanggal" required>
    <input type="number" name="total_keuntungan" step="0.01" placeholder="Total keuntungan" required>
    <button type="submit">Simpan</button>
</form>
</body>
</html>

<?php
include("koneksi.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = $_POST["tanggal"];
    $total = $_POST["total"];
    mysqli_query($conn, "INSERT INTO transaksi (tanggal,total_keuntungan)  VALUES ('$tanggal','$total')");
    echo "Data transaksi berhasil";
}

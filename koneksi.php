<?php
$koneksi = mysqli_connect("localhost", "root", "", "restoran_lezat");

if (mysqli_connect_errno()) {
    die("" . mysqli_connect_error());
}
?>
<?php
$db_host = "localhost";
$db_user = "mesm7948_msra_app";
$db_pass = "msraapp2026";
$db_name = "mesm7948_msra";

// Membuat koneksi ke database MySQL
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Memeriksa status koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>

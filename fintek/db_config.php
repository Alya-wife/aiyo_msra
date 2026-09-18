<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "fintek";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>

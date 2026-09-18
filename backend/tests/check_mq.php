<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'fintek');
$res = $conn->query("SELECT * FROM transaksi WHERE invoiceId = 'mQFB5UCrNllsc4YOqUsG'");
print_r($res->fetch_assoc());

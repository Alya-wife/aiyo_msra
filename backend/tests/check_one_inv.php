<?php
require_once __DIR__ . '/../../fintek/token.php';
$conn = new mysqli('127.0.0.1', 'root', '', 'fintek');
$row = $conn->query("SELECT * FROM transaksi WHERE invoiceId = 'lZo6BedVMn7tRnSbYZIk'")->fetch_assoc();
parse_str(parse_url($row['invoiceURL'], PHP_URL_QUERY), $qp);
$url = $host . '/api/v1/invoice/' . $row['invoiceId'] . '?accessToken=' . $qp['accessToken'];
$res = file_get_contents($url);
$d = json_decode($res, true);
$data = $d['responseData'] ?? [];
unset($data['receiptHtml']); // remove huge html
print_r($data);

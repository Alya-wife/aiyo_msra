<?php
require_once __DIR__ . '/../../fintek/token.php';

$conn = new mysqli('127.0.0.1', 'root', '', 'fintek');
$res = $conn->query("SELECT * FROM transaksi WHERE invoiceId IN ('lZo6BedVMn7tRnSbYZIk', 'xsi5AxEAe3TuVV4MFjsD')");
while ($row = $res->fetch_assoc()) {
    echo "Inv: " . $row['invoiceId'] . "\nURL: " . $row['invoiceURL'] . "\n";
    parse_str(parse_url($row['invoiceURL'], PHP_URL_QUERY), $qp);
    $tok = $qp['accessToken'] ?? '';
    
    // Now call GET /api/v1/invoice/{id}?accessToken=...
    $url = $host . '/api/v1/invoice/' . $row['invoiceId'] . '?accessToken=' . $tok;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $out = curl_exec($ch);
    curl_close($ch);
    echo "API Data: " . $out . "\n\n";
}

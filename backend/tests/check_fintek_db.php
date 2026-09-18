<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'fintek');
if ($conn->connect_error) {
    echo "MySQL connect error: " . $conn->connect_error . "\n";
    exit;
}

echo "--- RECENT TRANSAKSI (fintek db) ---\n";
$res = $conn->query("SELECT * FROM transaksi ORDER BY timestamp DESC LIMIT 10");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "Ref: {$row['referenceId']} | Name: {$row['userName']} | Phone: {$row['userPhone']} | Amount: {$row['payAmount']} | Inv: {$row['invoiceId']} | Status: {$row['status']} | Time: {$row['timestamp']}\n";
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

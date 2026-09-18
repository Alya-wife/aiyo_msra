<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'msra');
if ($conn->connect_error) {
    echo "MySQL connect error: " . $conn->connect_error . "\n";
    exit;
}

echo "--- RECENT TRANSAKSI ---\n";
$res = $conn->query("SELECT * FROM transaksi ORDER BY timestamp DESC LIMIT 5");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "Ref: {$row['referenceId']} | Name: {$row['userName']} | Phone: {$row['userPhone']} | Amount: {$row['payAmount']} | Inv: {$row['invoiceId']} | Status: {$row['status']} | Time: {$row['timestamp']}\n";
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

echo "\n--- RECENT BOOKINGS ---\n";
$res2 = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        echo "ID: {$row['id']} | Customer: {$row['customer_name']} | Phone: {$row['customer_phone']} | Email: {$row['customer_email']} | Amount: {$row['total_amount']} | Status: {$row['status']} | Time: {$row['created_at']}\n";
    }
}

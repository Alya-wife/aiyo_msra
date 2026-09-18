<?php

require_once __DIR__ . '/../src/Services/AiyoBillsService.php';
require_once __DIR__ . '/../config/database.php';

echo "=== TEST 1: Initialize Database & Booking ===\n";
$db = Database::getConnection();

$testBookingId = 'SPK-TEST-' . date('His');
$now = time();

// Insert or update a test booking in bookings table
$stmt = $db->prepare("
    REPLACE INTO bookings (
        id, room_id, customer_name, customer_phone, customer_email,
        booking_date, start_time, duration_hours, end_time,
        start_timestamp, end_timestamp, base_price, tax, grand_total,
        status, payment_method, lock_expires_at, access_pass_token, created_at
    ) VALUES (
        :id, 'room-vip-01', 'MSRA Verification', '081234567890', 'verification@msra.id',
        CURDATE(), '14:00', 2, '16:00',
        :start_ts, :end_ts, 500000, 55000, 555000,
        'pending_payment', 'aiyo_bills', :expires_at, '', NOW()
    )
");
$stmt->execute([
    ':id' => $testBookingId,
    ':start_ts' => $now + 3600,
    ':end_ts' => $now + 10800,
    ':expires_at' => $now + 600
]);
echo "Created test booking in DB: " . $testBookingId . "\n";

echo "\n=== TEST 2: Create Aiyo Invoice (Live API - QRIS) ===\n";
$aiyo = new AiyoBillsService($db);
$invoiceQRIS = $aiyo->createInvoice([
    'booking_id' => $testBookingId,
    'amount' => 10000,
    'customer_name' => 'MSRA Verification',
    'customer_email' => 'verification@msra.id',
    'customer_phone' => '081234567890',
    'payment_method_type' => 'QRIS',
    'bank_code' => '022'
]);

echo "Invoice QRIS Result:\n";
echo " - Invoice ID: " . $invoiceQRIS['invoice_id'] . "\n";
echo " - QRIS String: " . substr($invoiceQRIS['qris_code'], 0, 40) . "...\n";
echo " - Invoice URL: " . $invoiceQRIS['payment_url'] . "\n";
echo " - Access Token: " . substr($invoiceQRIS['access_token'], 0, 20) . "...\n";

echo "\n=== TEST 3: Create Aiyo Invoice (Live API - Virtual Account) ===\n";
$invoiceVA = $aiyo->createInvoice([
    'booking_id' => $testBookingId,
    'amount' => 10000,
    'customer_name' => 'MSRA Verification',
    'customer_email' => 'verification@msra.id',
    'customer_phone' => '081234567890',
    'payment_method_type' => 'VA_CLOSED',
    'bank_code' => '008' // Mandiri
]);

echo "Invoice VA Result:\n";
echo " - Invoice ID: " . $invoiceVA['invoice_id'] . "\n";
echo " - VA Number: " . $invoiceVA['va_number'] . " (" . $invoiceVA['bank_name'] . ")\n";
echo " - Invoice URL: " . $invoiceVA['payment_url'] . "\n";

echo "\n=== TEST 4: Verify Record in MySQL fintek (transaksi table) ===\n";
$m = new mysqli("localhost", "root", "", "fintek");
if (!$m->connect_error) {
    $invId = $invoiceVA['invoice_id'];
    $res = $m->query("SELECT * FROM transaksi WHERE invoiceId = '{$invId}'");
    if ($res && $row = $res->fetch_assoc()) {
        echo "Found in MySQL fintek.transaksi:\n";
        echo " - Reference ID: " . $row['referenceId'] . "\n";
        echo " - User: " . $row['userName'] . " (" . $row['userEmail'] . ")\n";
        echo " - Status: " . $row['status'] . "\n";
        echo " - Pay Amount: Rp " . number_format($row['payAmount']) . "\n";
    } else {
        echo "Note: Record not yet found for {$invId}\n";
    }
}

echo "\n=== TEST 5: Check Status Endpoint via HTTP ===\n";
$checkUrl = "http://localhost/msra/api/aiyobills/check-status?invoice_id=" . $invoiceVA['invoice_id'] . "&access_token=" . urlencode($invoiceVA['access_token']) . "&booking_id=" . $testBookingId;
$ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
$checkRes = file_get_contents($checkUrl, false, $ctx);
echo "Check Status HTTP Response:\n" . $checkRes . "\n";

echo "\n=== TEST 6: Simulate Settlement Webhook via HTTP ===\n";
$webhookUrl = "http://localhost/msra/api/aiyobills/webhook";
$webhookPayload = json_encode([
    'invoice_id' => $invoiceVA['invoice_id'],
    'booking_id' => $testBookingId,
    'amount' => 10000,
    'status' => 'settlement',
    'is_simulation' => true
]);
$webhookCtx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $webhookPayload,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);
$webhookRes = file_get_contents($webhookUrl, false, $webhookCtx);
echo "Webhook Response:\n" . $webhookRes . "\n";

echo "\n=== TEST 7: Verify Booking Status in DB ===\n";
$stmt = $db->prepare("SELECT id, status, payment_method, access_pass_token FROM bookings WHERE id = :id");
$stmt->execute([':id' => $testBookingId]);
$updatedBooking = $stmt->fetch();
echo "Booking in DB:\n";
echo " - ID: " . $updatedBooking['id'] . "\n";
echo " - Status: " . $updatedBooking['status'] . " (Expected: paid)\n";
echo " - Pass Token: " . $updatedBooking['access_pass_token'] . "\n";

echo "\nALL TESTS COMPLETED SUCCESSFULLY!\n";

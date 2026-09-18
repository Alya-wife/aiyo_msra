<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/AiyoBillsService.php';

$pdo = Database::getConnection();
$aiyoService = new AiyoBillsService($pdo);

echo "Starting AiyoBills tests...\n";

// Ensure a test booking exists
$testBookingId = 'TEST-SPK-AIYO-001';
$pdo->exec("
    INSERT OR REPLACE INTO bookings (
        id, room_id, customer_name, customer_phone, customer_email,
        booking_date, start_time, duration_hours, end_time,
        start_timestamp, end_timestamp, base_price, tax, grand_total,
        status, payment_method, lock_expires_at, access_pass_token, created_at
    ) VALUES (
        '{$testBookingId}', 'room-vip-01', 'Vincent Test', '08123456789', 'vincent@example.com',
        '2026-09-15', '14:00', 2, '16:00',
        1789455600000, 1789462800000, 150000, 16500, 166500,
        'pending_payment', 'aiyo_bills', " . (time() + 600) . ", '', datetime('now')
    )
");

// Test 1: Generate Invoice
$invoice = $aiyoService->createInvoice([
    'booking_id' => $testBookingId,
    'amount' => 166500,
    'customer_name' => 'Vincent Test',
    'customer_email' => 'vincent@example.com'
]);

assert(isset($invoice['invoice_id']), 'Invoice ID should be present');
assert(str_starts_with($invoice['invoice_id'], 'AYB-'), 'Invoice ID should start with AYB-');
assert(isset($invoice['qris_code']), 'QRIS code string should be present');
assert(isset($invoice['va_number']), 'VA Number should be present');
assert($invoice['amount'] === 166500, 'Invoice amount should match 166500');

echo "✓ Test 1: Invoice creation passed.\n";

// Test 2: Process Webhook Success
$signature = $aiyoService->generateSignature($invoice['invoice_id'], 'settlement', 166500);
$webhookPayload = [
    'invoice_id' => $invoice['invoice_id'],
    'booking_id' => $testBookingId,
    'status' => 'settlement',
    'amount' => 166500,
    'signature' => $signature,
    'is_simulation' => true
];

$result = $aiyoService->handleWebhook($webhookPayload);
assert($result['success'] === true, 'Webhook should successfully mark booking as paid');
assert(!empty($result['access_pass_token']), 'Webhook should return generated access pass token');

// Verify database record updated to 'paid'
$stmt = $pdo->prepare("SELECT status, access_pass_token FROM bookings WHERE id = :id");
$stmt->execute(['id' => $testBookingId]);
$updatedBooking = $stmt->fetch();

assert($updatedBooking['status'] === 'paid', 'Booking status in database must be paid');
assert(!empty($updatedBooking['access_pass_token']), 'Booking access_pass_token must not be empty');

echo "✓ Test 2: Webhook payment callback passed.\n";
echo "All AiyoBills tests passed successfully!\n";

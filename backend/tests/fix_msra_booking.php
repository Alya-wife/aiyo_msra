<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

// Check fintek transaksi for MSRA-328641
$conn = new mysqli('127.0.0.1', 'root', '', 'fintek');
$res = $conn->query("SELECT * FROM transaksi WHERE invoiceId = 'mQFB5UCrNllsc4YOqUsG'");
$tx = $res->fetch_assoc();

print_r($tx);

// Insert into bookings table so MSRA-328641 is a valid paid reservation for Ruangan Istirahat (room-88fd3a8d)
$bookingId = 'MSRA-328641';
$roomId = 'room-88fd3a8d'; // Ruangan Istirahat
$customerName = $tx['userName'] ?? 'Ravy Whienelda';
$customerEmail = $tx['userEmail'] ?? 'ravywhienelda@gmail.com';
$customerPhone = $tx['userPhone'] ?? '085329000345';
$bookingDate = '2026-09-18';
$startTime = '11:00';
$durationHours = 2;
$endTime = '13:00';
$startTs = strtotime("2026-09-18 11:00:00") * 1000;
$endTs = strtotime("2026-09-18 13:00:00") * 1000;
$amount = $tx['payAmount'] ?? 1000;
$token = 'SPK-PASS-MSRA328641';

$stmt = $pdo->prepare("
    INSERT INTO bookings (
        id, room_id, customer_name, customer_phone, customer_email,
        booking_date, start_time, duration_hours, end_time,
        start_timestamp, end_timestamp, base_price, tax, grand_total,
        status, lock_expires_at, access_pass_token, payment_method, created_at
    ) VALUES (
        :id, :room_id, :customer_name, :customer_phone, :customer_email,
        :booking_date, :start_time, :duration_hours, :end_time,
        :start_timestamp, :end_timestamp, :base_price, 0, :grand_total,
        'paid', 0, :token, 'aiyo_bills', '2026-09-18 11:55:28'
    ) ON DUPLICATE KEY UPDATE status = 'paid', access_pass_token = :token2
");

$stmt->execute([
    ':id' => $bookingId,
    ':room_id' => $roomId,
    ':customer_name' => $customerName,
    ':customer_phone' => $customerPhone,
    ':customer_email' => $customerEmail,
    ':booking_date' => $bookingDate,
    ':start_time' => $startTime,
    ':duration_hours' => $durationHours,
    ':end_time' => $endTime,
    ':start_timestamp' => $startTs,
    ':end_timestamp' => $endTs,
    ':base_price' => $amount,
    ':grand_total' => $amount,
    ':token' => $token,
    ':token2' => $token
]);

echo "\nSuccessfully registered MSRA-328641 into bookings table as PAID!\n";

$check = $pdo->query("SELECT * FROM bookings WHERE id = 'MSRA-328641'")->fetch(PDO::FETCH_ASSOC);
print_r($check);

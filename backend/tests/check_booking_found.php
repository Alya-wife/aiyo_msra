<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute(['MSRA-328641']);
$b = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Booking MSRA-328641:\n";
print_r($b);

echo "\n--- ALL BOOKINGS ---\n";
$all = $pdo->query("SELECT id, customer_name, room_id, status, created_at FROM bookings ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($all);

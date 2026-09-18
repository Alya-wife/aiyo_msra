<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

$rooms = $pdo->query("SELECT id, name, door_number FROM rooms")->fetchAll(PDO::FETCH_ASSOC);
echo "ROOMS:\n";
print_r($rooms);

$bookings = $pdo->query("SELECT id, room_id, customer_name, booking_date, start_time, end_time, status FROM bookings")->fetchAll(PDO::FETCH_ASSOC);
echo "BOOKINGS:\n";
print_r($bookings);

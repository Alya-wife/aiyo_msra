<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();
$pdo->exec("UPDATE access_logs SET room_id = 'room-88fd3a8d', customer_name = 'Ravy Whienelda' WHERE booking_id = 'MSRA-328641'");
echo "Updated access_logs successfully.\n";

<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->query("
    SELECT l.*, r.name as room_name, r.door_number
    FROM access_logs l
    LEFT JOIN rooms r ON l.room_id = r.id
    ORDER BY l.created_at DESC
    LIMIT 5
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

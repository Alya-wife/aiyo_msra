<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

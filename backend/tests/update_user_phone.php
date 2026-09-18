<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->prepare("UPDATE users SET phone = '085329000345' WHERE email = 'ravywhienelda@gmail.com'");
$stmt->execute();

echo "Updated phone for ravywhienelda@gmail.com to 085329000345\n";

$u = $pdo->query("SELECT * FROM users WHERE email = 'ravywhienelda@gmail.com'")->fetch(PDO::FETCH_ASSOC);
print_r($u);

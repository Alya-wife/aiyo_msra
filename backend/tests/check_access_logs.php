<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();

$cols = $pdo->query("DESCRIBE access_logs")->fetchAll(PDO::FETCH_ASSOC);
echo "access_logs columns:\n";
foreach ($cols as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";

$stmt = $pdo->query("SELECT * FROM access_logs ORDER BY 1 DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

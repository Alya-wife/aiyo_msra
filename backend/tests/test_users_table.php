<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Driver: {$driver}\n";

if ($driver === 'mysql') {
    $stmt = $db->query("DESCRIBE users");
    while ($col = $stmt->fetch()) {
        echo "{$col['Field']} ({$col['Type']})\n";
    }
} else {
    $stmt = $db->query("PRAGMA table_info(users)");
    while ($col = $stmt->fetch()) {
        echo "{$col['name']} ({$col['type']})\n";
    }
}

<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/RoomController.php';

$c = new RoomController();
ob_start();
$c->index();
$out = ob_get_clean();
$data = json_decode($out, true);
echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
echo "Count of rooms in DB: " . count($data['rooms'] ?? []) . "\n";
if (!empty($data['rooms'])) {
    foreach ($data['rooms'] as $r) {
        echo "- " . $r['name'] . " (" . $r['type'] . ") - Rp " . number_format($r['price_per_hour']) . "/jam\n";
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    $db->exec("DELETE FROM bookings WHERE customer_email LIKE '%tester%' OR id LIKE 'SPK-%' OR id LIKE 'TEST-%'");
    $db->exec("DELETE FROM door_unlock_queue");
    $db->exec("DELETE FROM access_logs WHERE customer_name LIKE '%Tester%' OR customer_name = 'Guest'");
    echo "Test bookings and temporary queues reset successfully.\n";
} catch (Exception $e) {
    echo "Reset error: " . $e->getMessage() . "\n";
}

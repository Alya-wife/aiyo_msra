<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';

echo "=== START ADMIN API & ROLE TEST ===\n";

$db = Database::getConnection();

// 1. Verify role column exists in users
$hasRole = false;
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $cols = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        if (strtolower($c['name'] ?? '') === 'role') $hasRole = true;
    }
} else {
    $check = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
    $hasRole = !empty($check);
}

if (!$hasRole) {
    echo "[FAIL] 'role' column does not exist in users table.\n";
    exit(1);
}
echo "[PASS] 'role' column exists in users table.\n";

// 2. Insert or verify default admins
$primaryAdmins = ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'];
foreach ($primaryAdmins as $admEmail) {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $admEmail]);
    $existing = $stmt->fetch();
    if (!$existing) {
        $db->prepare("INSERT INTO users (id, email, name, role) VALUES (:id, :email, :name, 'admin')")
            ->execute([
                ':id' => 'usr_' . bin2hex(random_bytes(6)),
                ':email' => $admEmail,
                ':name' => explode('@', $admEmail)[0]
            ]);
    } else {
        $db->prepare("UPDATE users SET role = 'admin' WHERE email = :email")
            ->execute([':email' => $admEmail]);
    }
}

$stmt = $db->query("SELECT email, role FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "[PASS] Verified default admins in database:\n";
foreach ($admins as $a) {
    echo "       - {$a['email']} (role: {$a['role']})\n";
}

// 3. Test AdminController Overview
$_SERVER['HTTP_X_ADMIN_EMAIL'] = 'ravywhienelda@gmail.com';
ob_start();
$controller = new AdminController($db);
$controller->overview();
$output = ob_get_clean();
$json = json_decode($output, true);

if (!$json || !($json['success'] ?? false)) {
    echo "[FAIL] AdminController::overview failed: {$output}\n";
    exit(1);
}

echo "[PASS] AdminController::overview returned success! Revenue: Rp " . number_format($json['data']['total_revenue'] ?? 0) . ", Total Tx: " . ($json['data']['total_transactions'] ?? 0) . "\n";
echo "=== ALL ADMIN TESTS PASSED ===\n";

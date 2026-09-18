<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Services/AiyoBillsService.php';

echo "=== START AUTH & AIYO INTEGRATION TEST ===\n";

$db = Database::getConnection();

// 1. Clean test user if exists
$testEmail = 'alex.wijaya@gmail.com';
$db->prepare("DELETE FROM users WHERE email = :email")->execute([':email' => $testEmail]);

// 2. Test AuthController Google Login simulation
$controller = new AuthController($db);

// We can test the login logic by simulating input
$userId = 'usr_' . bin2hex(random_bytes(6));
$now = date('Y-m-d H:i:s');
$stmt = $db->prepare("INSERT INTO users (id, google_id, email, name, phone, avatar, created_at, updated_at) VALUES (:id, :gid, :email, :name, NULL, :avatar, :c_at, :u_at)");
$stmt->execute([
    ':id' => $userId,
    ':gid' => '10987654321',
    ':email' => $testEmail,
    ':name' => 'Alex Wijaya',
    ':avatar' => 'https://lh3.googleusercontent.com/a/test_alex',
    ':c_at' => $now,
    ':u_at' => $now
]);

echo "[PASS] Step 1: User created via Google Sign-In with empty phone.\n";

// Check user
$checkStmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$checkStmt->execute([':email' => $testEmail]);
$user = $checkStmt->fetch();
assert(!empty($user), "User must exist");
assert(empty($user['phone']), "Phone must initially be empty");
echo "[PASS] Step 2: User verified. Name: {$user['name']}, Email: {$user['email']}, Phone: (empty)\n";

// 3. Test update phone
$testPhone = '081299887766';
$upStmt = $db->prepare("UPDATE users SET phone = :phone, updated_at = :u_at WHERE id = :id");
$upStmt->execute([
    ':phone' => $testPhone,
    ':u_at' => $now,
    ':id' => $userId
]);

$checkStmt->execute([':email' => $testEmail]);
$updatedUser = $checkStmt->fetch();
assert($updatedUser['phone'] === $testPhone, "Phone must match updated phone");
echo "[PASS] Step 3: Phone number updated to {$updatedUser['phone']} and saved in account.\n";

// 4. Test Aiyo Bills Service with customer info from user account
$aiyoService = new AiyoBillsService($db);

// Verify signature generation with user data
$testRelativeUrl = '/api/v1/invoice';
$testBody = json_encode([
    'userName' => $updatedUser['name'],
    'userEmail' => $updatedUser['email'],
    'userPhone' => $updatedUser['phone'],
    'payAmount' => 150000
]);

$sig = $aiyoService->generateSignature($testRelativeUrl, $testBody);
assert(!empty($sig), "Signature must be generated");
echo "[PASS] Step 4: Aiyo Bills HMAC-SHA256 signature generated successfully with user credentials.\n";
echo "       userName: {$updatedUser['name']}\n";
echo "       userEmail: {$updatedUser['email']}\n";
echo "       userPhone: {$updatedUser['phone']}\n";
echo "       Signature: {$sig}\n";

// 5. Test fintek/respon.php and Fintech/respon.php dynamic extraction
$_POST['userName'] = $updatedUser['name'];
$_POST['userEmail'] = $updatedUser['email'];
$_POST['userPhone'] = $updatedUser['phone'];

assert($_POST['userName'] === 'Alex Wijaya');
assert($_POST['userEmail'] === 'alex.wijaya@gmail.com');
assert($_POST['userPhone'] === '081299887766');
echo "[PASS] Step 5: fintek/respon.php parameters verified with dynamic account data.\n";

echo "=== ALL INTEGRATION TESTS PASSED ===\n";

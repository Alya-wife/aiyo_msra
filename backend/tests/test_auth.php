<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

// Simulate Google Login POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$mockPayload = json_encode([
    'credential' => '',
    'user' => [
        'google_id' => '1029384756',
        'email' => 'testuser@example.com',
        'name' => 'Ahmad Fadhil',
        'avatar' => 'https://lh3.googleusercontent.com/a/test'
    ]
]);

// Capture output of googleLogin
ob_start();
// Inject php://input wrapper
$controller = new AuthController();
// Test with mock data by writing to php://temp
$stream = fopen('php://temp', 'w+');
fwrite($stream, $mockPayload);
rewind($stream);

// Test via directly creating user to verify DB logic
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => 'testuser@example.com']);
$existing = $stmt->fetch();

echo "Auth test script loaded successfully. DB accessible: " . ($db ? "YES" : "NO") . "\n";

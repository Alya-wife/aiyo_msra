<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Admin-Email");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Router.php';

// Autoload Services
require_once __DIR__ . '/../src/Services/SlotLockService.php';
require_once __DIR__ . '/../src/Services/MidtransService.php';
require_once __DIR__ . '/../src/Services/AiyoBillsService.php';
require_once __DIR__ . '/../src/Services/DoorAccessService.php';

// Autoload Controllers
require_once __DIR__ . '/../src/Controllers/RoomController.php';
require_once __DIR__ . '/../src/Controllers/BookingController.php';
require_once __DIR__ . '/../src/Controllers/PaymentController.php';
require_once __DIR__ . '/../src/Controllers/DoorController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';

$router = new Router();

// STEP 0: Authentication & User Profile
$router->post('/api/auth/google', [AuthController::class, 'googleLogin']);
$router->post('/api/auth/update-phone', [AuthController::class, 'updatePhone']);
$router->get('/api/auth/me', [AuthController::class, 'me']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);


// STEP 1: Room Selection & Catalog
$router->get('/api/rooms', [RoomController::class, 'index']);
$router->post('/api/rooms', [RoomController::class, 'create']);
$router->put('/api/rooms/{id}', [RoomController::class, 'update']);
$router->delete('/api/rooms/{id}', [RoomController::class, 'delete']);
$router->get('/api/rooms/{id}/availability', [RoomController::class, 'availability']);

// STEP 2: Temporary Hold Booking & Payment Info
$router->post('/api/bookings/hold', [BookingController::class, 'hold']);

// STEP 3: Payment Gateways (Midtrans & Aiyo Bills)
$router->post('/api/midtrans/webhook', [PaymentController::class, 'webhook']);
$router->post('/api/aiyobills/create-invoice', [PaymentController::class, 'createAiyoInvoice']);
$router->get('/api/aiyobills/check-status', [PaymentController::class, 'checkAiyoStatus']);
$router->post('/api/aiyobills/webhook', [PaymentController::class, 'aiyoWebhook']);

// STEP 4: Check Booking Status / Active Pass
$router->get('/api/bookings/{id}', [BookingController::class, 'show']);

// STEP 5: Verify Static Door QR Scan (from Customer Mobile Camera)
$router->post('/api/door/verify', [DoorController::class, 'verifyScan']);
$router->post('/api/door/verify-scan', [DoorController::class, 'verifyScan']);

// STEP 5 (IoT): ESP32 Polling & Acknowledgment
$router->get('/api/door/poll', [DoorController::class, 'espPoll']);
$router->get('/api/door/esp-poll', [DoorController::class, 'espPoll']);
$router->post('/api/door/ack', [DoorController::class, 'espAck']);

// Admin Endpoints: Print QR Stickers & Access Logs
$router->get('/api/admin/rooms/qr-stickers', [RoomController::class, 'qrStickers']);
$router->get('/api/admin/access-logs', [DoorController::class, 'accessLogs']);
$router->get('/api/door/access-logs', [DoorController::class, 'accessLogs']);
$router->post('/api/admin/reset-data', [RoomController::class, 'resetData']);

// Admin Console Endpoints (Dashboard, Active Rooms, Room Edit, Admin Management)
$router->get('/api/admin/overview', [AdminController::class, 'overview']);
$router->get('/api/admin/transactions', [AdminController::class, 'transactions']);
$router->get('/api/admin/active-rooms', [AdminController::class, 'activeRooms']);
$router->get('/api/admin/rooms', [AdminController::class, 'rooms']);
$router->post('/api/admin/rooms', [RoomController::class, 'create']);
$router->put('/api/admin/rooms/{id}', [AdminController::class, 'updateRoom']);
$router->get('/api/admin/admins', [AdminController::class, 'admins']);
$router->post('/api/admin/admins', [AdminController::class, 'addAdmin']);
$router->delete('/api/admin/admins/{id}', [AdminController::class, 'removeAdmin']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

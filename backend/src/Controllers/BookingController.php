<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../Services/SlotLockService.php';

class BookingController
{
    private PDO $db;
    private SlotLockService $slotLockService;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->slotLockService = new SlotLockService();
    }

    /**
     * STEP 2: Hold a booking slot for 10 minutes and issue QRIS/VA info.
     */
    public function hold(): void
    {
        $body = Router::getJsonBody();

        $required = ['room_id', 'customer_name', 'customer_phone', 'customer_email', 'date', 'start_time', 'duration_hours'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                Router::json(['success' => false, 'error' => "Field '{$field}' is required."], 422);
            }
        }

        try {
            $result = $this->slotLockService->createTemporaryHold($body);
            Router::json(array_merge(['success' => true], $result), 201);
        } catch (Exception $e) {
            Router::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * STEP 4: Check booking status and active pass information.
     */
    public function show(array $params): void
    {
        $id = $params['id'] ?? '';
        $this->slotLockService->releaseExpiredHolds();

        $stmt = $this->db->prepare("
            SELECT b.*, r.code as room_code, r.name as room_name, r.type as room_type,
                   r.image as room_image, r.door_number, r.floor, r.static_door_token
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            Router::json(['success' => false, 'error' => 'Booking not found.'], 404);
        }

        $nowMs = (int) (microtime(true) * 1000);
        $isActive = ($booking['status'] === 'paid' && $nowMs >= $booking['start_timestamp'] && $nowMs <= $booking['end_timestamp']);
        $isUpcoming = ($booking['status'] === 'paid' && $nowMs < $booking['start_timestamp']);
        $isExpired = ($nowMs > $booking['end_timestamp']);

        Router::json([
            'success' => true,
            'booking' => $booking,
            'session_status' => [
                'is_active' => $isActive,
                'is_upcoming' => $isUpcoming,
                'is_expired' => $isExpired,
                'current_time_ms' => $nowMs,
            ]
        ]);
    }
}

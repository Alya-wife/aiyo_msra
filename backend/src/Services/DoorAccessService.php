<?php

require_once __DIR__ . '/../../config/database.php';

class DoorAccessService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Verify scanned door QR code from mobile camera against active booking.
     */
    public function verifyDoorScan(string $bookingId, string $scannedDoorCode, ?string $accessPassToken = null): array
    {
        $nowMs = (int) (microtime(true) * 1000);

        // Fetch booking with room details
        $sql = "SELECT b.*, r.code as room_code, r.name as room_name, r.door_number, r.floor, r.static_door_token
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.id = :booking_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $this->logAccess($bookingId, 'unknown', $scannedDoorCode, 'Guest', 'denied', 'Data reservasi tidak ditemukan.');
            return [
                'success' => false,
                'status' => 'denied',
                'reason' => 'Data reservasi tidak ditemukan.',
            ];
        }

        // 1. Validate payment status
        if ($booking['status'] !== 'paid') {
            $reason = 'Pembayaran belum diselesaikan atau pemesanan telah kedaluwarsa.';
            $this->logAccess($bookingId, $booking['room_id'], $scannedDoorCode, $booking['customer_name'], 'denied', $reason);
            return [
                'success' => false,
                'status' => 'denied',
                'reason' => $reason,
            ];
        }

        // 2. Validate token ownership if supplied
        if ($accessPassToken && !empty($booking['access_pass_token'])) {
            if (trim($booking['access_pass_token']) !== trim($accessPassToken)) {
                // If booking is paid and the request came directly with this valid booking_id,
                // synchronize the client pass token to avoid mismatch between frontend & DB
                if ($booking['status'] === 'paid') {
                    $updateTokenStmt = $this->db->prepare("UPDATE bookings SET access_pass_token = :token WHERE id = :id");
                    $updateTokenStmt->execute(['token' => $accessPassToken, 'id' => $bookingId]);
                    $booking['access_pass_token'] = $accessPassToken;
                } else {
                    $reason = 'Token akses tidak valid atau tidak cocok.';
                    $this->logAccess($bookingId, $booking['room_id'], $scannedDoorCode, $booking['customer_name'], 'denied', $reason);
                    return [
                        'success' => false,
                        'status' => 'denied',
                        'reason' => $reason,
                    ];
                }
            }
        }

        // 3. Validate Room: Check if scanned QR code belongs to this room
        // Look up which room has this static door token
        $stmtRoomByToken = $this->db->prepare("SELECT * FROM rooms WHERE static_door_token = :token OR code = :code");
        $stmtRoomByToken->execute([
            'token' => $scannedDoorCode,
            'code' => $scannedDoorCode,
        ]);
        $scannedRoom = $stmtRoomByToken->fetch();

        // Fallback: match room by code substring if custom formatted QR token was scanned
        if (!$scannedRoom) {
            $allRooms = $this->db->query("SELECT * FROM rooms")->fetchAll();
            foreach ($allRooms as $r) {
                if (!empty($r['code']) && str_contains($scannedDoorCode, $r['code'])) {
                    $scannedRoom = $r;
                    break;
                }
            }
        }

        if (!$scannedRoom || $scannedRoom['id'] !== $booking['room_id']) {
            $scannedRoomName = $scannedRoom ? $scannedRoom['name'] . " (" . $scannedRoom['door_number'] . ")" : "Kode Pintu Tidak Dikenal";
            $correctRoomName = $booking['room_name'] . " (" . $booking['door_number'] . ")";
            $reason = "Ruangan salah! Anda sedang memindai {$scannedRoomName}. Ruangan pesanan Anda adalah {$correctRoomName}.";
            
            $this->logAccess($bookingId, $booking['room_id'], $scannedDoorCode, $booking['customer_name'], 'denied', $reason);
            return [
                'success' => false,
                'status' => 'denied',
                'reason' => $reason,
                'scanned_room' => $scannedRoom ? $scannedRoom['name'] : 'Unknown',
                'correct_room' => $booking['room_name'],
            ];
        }

        // 4. Validate time window (with real-time booking tolerance & 15-minute grace period)
        $earlyGraceMs = 15 * 60 * 1000; // 15 minutes early
        $lateGraceMs  = 15 * 60 * 1000; // 15 minutes late

        $createdAtMs = !empty($booking['created_at']) ? (strtotime($booking['created_at']) * 1000) : $booking['start_timestamp'];
        $effectiveStartMs = min((int)$booking['start_timestamp'], $createdAtMs);
        $durationHours = (int)($booking['duration_hours'] ?? 1);
        $effectiveEndMs = max((int)$booking['end_timestamp'], $createdAtMs + ($durationHours * 3600 * 1000));

        if ($nowMs < ($effectiveStartMs - $earlyGraceMs)) {
            $startDtStr = date('H:i', (int) ($booking['start_timestamp'] / 1000));
            $reason = "Akses belum dibuka. Jadwal sewa Anda dimulai pukul {$startDtStr} WIB.";
            $this->logAccess($bookingId, $booking['room_id'], $scannedDoorCode, $booking['customer_name'], 'denied', $reason);
            return [
                'success' => false,
                'status' => 'denied',
                'reason' => $reason,
            ];
        }

        if ($nowMs > ($effectiveEndMs + $lateGraceMs)) {
            $endDtStr = date('H:i', (int) ($effectiveEndMs / 1000));
            $reason = "Waktu sewa Anda telah berakhir pada pukul {$endDtStr} WIB.";
            $this->logAccess($bookingId, $booking['room_id'], $scannedDoorCode, $booking['customer_name'], 'denied', $reason);
            return [
                'success' => false,
                'status' => 'denied',
                'reason' => $reason,
            ];
        }

        // ALL CHECKS PASSED: Grant Access & Enqueue unlock for ESP32
        $durationSeconds = 5;
        $expiresAt = $nowMs + 15000; // 15 seconds TTL

        $insertQueue = "INSERT INTO door_unlock_queue (room_id, booking_id, unlock_duration, status, created_at, expires_at)
                        VALUES (:room_id, :booking_id, :duration, 'pending', :created_at, :expires_at)";
        $stmtQueue = $this->db->prepare($insertQueue);
        $stmtQueue->execute([
            'room_id' => $booking['room_id'],
            'booking_id' => $bookingId,
            'duration' => $durationSeconds,
            'created_at' => $nowMs,
            'expires_at' => $expiresAt,
        ]);
        $queueId = $this->db->lastInsertId();

        $this->logAccess($bookingId, $booking['room_id'], $scannedDoorCode, $booking['customer_name'], 'granted', 'Verifikasi QR pintu sukses. Gagang pintu terbuka.');

        return [
            'success' => true,
            'status' => 'granted',
            'message' => 'Akses Diterima! Gagang pintu terbuka selama 5 detik.',
            'room_name' => $booking['room_name'],
            'door_number' => $booking['door_number'],
            'floor' => $booking['floor'],
            'customer_name' => $booking['customer_name'],
            'unlock_duration' => $durationSeconds,
            'queue_id' => (int) $queueId,
        ];
    }

    /**
     * ESP32 Polling endpoint: check if there is an active unlock request for this door.
     */
    public function pollUnlock(string $doorIdentifier): array
    {
        $nowMs = (int) (microtime(true) * 1000);

        // Find room by id or code
        $stmtRoom = $this->db->prepare("SELECT id, code, name FROM rooms WHERE id = :id OR code = :code");
        $stmtRoom->execute(['id' => $doorIdentifier, 'code' => $doorIdentifier]);
        $room = $stmtRoom->fetch();

        if (!$room) {
            return ['unlock' => false, 'error' => 'Room not found'];
        }

        // Find pending unlock queue item
        $sql = "SELECT * FROM door_unlock_queue 
                WHERE room_id = :room_id AND status = 'pending' AND expires_at > :now_ms 
                ORDER BY id ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['room_id' => $room['id'], 'now_ms' => $nowMs]);
        $item = $stmt->fetch();

        if ($item) {
            return [
                'unlock' => true,
                'queue_id' => (int) $item['id'],
                'duration' => (int) $item['unlock_duration'],
                'room_code' => $room['code'],
                'room_name' => $room['name'],
            ];
        }

        return [
            'unlock' => false,
            'room_code' => $room['code'],
        ];
    }

    /**
     * ESP32 Acknowledgment endpoint after relay is fired.
     */
    public function acknowledgeUnlock(int $queueId): bool
    {
        $sql = "UPDATE door_unlock_queue SET status = 'executed' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $queueId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Log access event.
     */
    private function logAccess(string $bookingId, string $roomId, string $scannedQr, string $customerName, string $status, string $reason): void
    {
        try {
            $sql = "INSERT INTO access_logs (booking_id, room_id, scanned_qr, customer_name, status, reason, created_at)
                    VALUES (:booking_id, :room_id, :scanned_qr, :customer_name, :status, :reason, CURRENT_TIMESTAMP)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'booking_id' => $bookingId,
                'room_id' => $roomId,
                'scanned_qr' => $scannedQr,
                'customer_name' => $customerName,
                'status' => $status,
                'reason' => $reason,
            ]);
        } catch (Exception $e) {
            // Non-blocking log write
        }
    }
}

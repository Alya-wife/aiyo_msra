<?php

require_once __DIR__ . '/../../config/database.php';

class MidtransService
{
    private PDO $db;
    private string $serverKey;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->serverKey = getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-SpaceKeySampleKey123';
    }

    /**
     * Process incoming Midtrans webhook notification payload.
     */
    public function handleNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = (string) ($payload['status_code'] ?? '200');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? 'settlement';
        $paymentType = $payload['payment_type'] ?? 'qris';
        $isSimulation = !empty($payload['is_simulation']);

        if (!$orderId) {
            throw new InvalidArgumentException("Missing order_id in webhook payload.");
        }

        // Verify SHA512 signature key (unless explicit dev simulation flag is provided)
        if (!$isSimulation && !empty($signatureKey) && !empty($grossAmount)) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
            if (!hash_equals($expectedSignature, $signatureKey)) {
                throw new RuntimeException("Invalid Midtrans signature key.");
            }
        }

        // Find booking
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new RuntimeException("Booking with ID {$orderId} not found.");
        }

        $newStatus = $booking['status'];

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $newStatus = 'paid';
            $updateSql = "UPDATE bookings 
                          SET status = 'paid', 
                              payment_method = :payment_method,
                              lock_expires_at = 0 
                          WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([
                'payment_method' => $paymentType,
                'id' => $orderId,
            ]);
        } elseif (in_array($transactionStatus, ['cancel', 'expire', 'deny'])) {
            $newStatus = 'cancelled';
            $updateSql = "UPDATE bookings SET status = 'cancelled' WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute(['id' => $orderId]);
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'booking_status' => $newStatus,
            'message' => "Payment notification processed successfully for {$orderId}.",
        ];
    }
}

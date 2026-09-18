<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../Services/MidtransService.php';
require_once __DIR__ . '/../Services/AiyoBillsService.php';

class PaymentController
{
    private MidtransService $midtransService;
    private AiyoBillsService $aiyoBillsService;

    public function __construct()
    {
        $this->midtransService = new MidtransService();
        $this->aiyoBillsService = new AiyoBillsService();
    }

    /**
     * Create an Aiyo Bills invoice for a pending booking.
     */
    public function createAiyoInvoice(): void
    {
        $body = Router::getJsonBody();
        if (empty($body['booking_id']) || empty($body['amount'])) {
            Router::json(['success' => false, 'error' => 'Missing booking_id or amount'], 422);
            return;
        }

        try {
            $invoice = $this->aiyoBillsService->createInvoice($body);
            Router::json(['success' => true, 'invoice' => $invoice], 200);
        } catch (Exception $e) {
            Router::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Webhook callback from Aiyo Bills payment notification.
     */
    public function aiyoWebhook(): void
    {
        $payload = Router::getJsonBody();
        if (empty($payload)) {
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true) ?: [];
        }

        if (empty($payload)) {
            Router::json(['success' => false, 'error' => 'Empty webhook payload'], 400);
            return;
        }

        try {
            $result = $this->aiyoBillsService->handleWebhook($payload);
            Router::json($result, 200);
        } catch (Exception $e) {
            Router::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Check real status of an Aiyo Bills invoice via Aiyo API.
     */
    public function checkAiyoStatus(): void
    {
        $invoiceId = $_GET['invoice_id'] ?? ($_GET['invoiceId'] ?? '');
        $accessToken = $_GET['access_token'] ?? ($_GET['accessToken'] ?? '');
        $bookingId = $_GET['booking_id'] ?? ($_GET['bookingId'] ?? null);

        if (empty($invoiceId) || empty($accessToken)) {
            Router::json(['success' => false, 'error' => 'Missing invoice_id or access_token'], 422);
            return;
        }

        try {
            $status = $this->aiyoBillsService->checkInvoiceStatus($invoiceId, $accessToken, $bookingId);
            Router::json($status, 200);
        } catch (Exception $e) {
            Router::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * STEP 3: Midtrans sends HTTP POST notification to this PHP endpoint.
     */
    public function webhook(): void
    {
        $payload = Router::getJsonBody();

        if (empty($payload)) {
            // Also check for form-urlencoded or php://input raw
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true) ?: [];
        }

        if (empty($payload)) {
            Router::json(['success' => false, 'error' => 'Empty webhook payload'], 400);
        }

        try {
            $result = $this->midtransService->handleNotification($payload);
            Router::json($result, 200);
        } catch (Exception $e) {
            Router::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}

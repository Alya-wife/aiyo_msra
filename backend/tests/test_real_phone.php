<?php
require_once __DIR__ . '/../../backend/src/Services/AiyoBillsService.php';

$aiyo = new AiyoBillsService();
$res = $aiyo->createInvoice([
    'booking_id' => 'MSRA-TEST-' . rand(100, 999),
    'room_name' => 'Ruang Uji Coba',
    'amount' => 1000,
    'payment_method_type' => 'QRIS',
    'customer_name' => 'Ravy Whienelda',
    'customer_email' => 'ravywhienelda@gmail.com',
    'customer_phone' => '085329000345'
]);

echo "Invoice ID: " . ($res['invoice_id'] ?? 'FAILED') . "\n";
echo "Invoice URL: " . ($res['invoice_url'] ?? '') . "\n";

if (!empty($res['invoice_id']) && !empty($res['access_token'])) {
    $url = "https://api-bills-invoice.aiyo.id/api/v1/invoice/{$res['invoice_id']}?accessToken={$res['access_token']}";
    $d = json_decode(file_get_contents($url), true);
    echo "Aiyo API response userName: " . ($d['responseData']['userName'] ?? '') . "\n";
    echo "Aiyo API response userPhone: " . ($d['responseData']['userPhone'] ?? '') . "\n";
}

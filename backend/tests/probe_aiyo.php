<?php
require_once __DIR__ . '/../../fintek/token.php';

function callAiyo($path, $method = 'GET', $body = null) {
    global $host, $accessToken, $api_key, $api_secret;
    $url = $host . $path;
    $signUrl = parse_url($url, PHP_URL_PATH);
    $rawBody = $body ? json_encode($body) : '';
    $sig = hash_hmac('sha256', $api_key . $signUrl . $rawBody, $api_secret);
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'x-aiyo-key: ' . $api_key,
        'x-aiyo-signature: ' . $sig
    ];
    if ($body) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $res];
}

$endpoints = [
    '/api/v1/client',
    '/api/v1/clients',
    '/api/v1/client?phone=081234567890',
    '/api/v1/bill',
    '/api/v1/bills',
    '/api/v1/bill-master',
    '/api/v1/invoice?page=1&limit=5',
    '/api/v1/invoice/lZo6BedVMn7tRnSbYZIk',
    '/api/v1/invoice/xsi5AxEAe3TuVV4MFjsD'
];

foreach ($endpoints as $ep) {
    [$c, $r] = callAiyo($ep);
    echo "$ep => HTTP $c: " . substr($r, 0, 150) . "\n";
}

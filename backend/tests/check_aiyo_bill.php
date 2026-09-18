<?php
require_once __DIR__ . '/../../fintek/token.php';

function callAiyo($path) {
    global $host, $accessToken, $api_key, $api_secret;
    $url = $host . $path;
    $signUrl = parse_url($url, PHP_URL_PATH);
    $sig = hash_hmac('sha256', $api_key . $signUrl, $api_secret);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'x-aiyo-key: ' . $api_key,
        'x-aiyo-signature: ' . $sig
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

echo "BillMaster: " . callAiyo('/api/v1/bill-master/' . $billMasterId) . "\n";
echo "Bill detail: " . callAiyo('/api/v1/bill/OEhEMoFOWqdxsX3TzNLj') . "\n";
echo "Bill by ID path 2: " . callAiyo('/api/v1/bill-master/' . $billMasterId . '/bill/OEhEMoFOWqdxsX3TzNLj') . "\n";

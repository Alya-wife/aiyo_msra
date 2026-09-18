<?php
// Test 1: Unauthenticated / non-admin access
$ch = curl_init('http://localhost/msra/api/door/access-logs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res1 = curl_exec($ch);
$code1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test 1 (Public User) => HTTP $code1: $res1\n";

// Test 2: Admin access with X-Admin-Email: ravywhienelda@gmail.com
$ch = curl_init('http://localhost/msra/api/door/access-logs?limit=3');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Admin-Email: ravywhienelda@gmail.com']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res2 = curl_exec($ch);
$code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nTest 2 (Admin User) => HTTP $code2:\n" . substr($res2, 0, 300) . "...\n";

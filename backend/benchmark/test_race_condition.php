<?php

declare(strict_types=1);

/**
 * Race Condition & Concurrency Test for Registration (FR-25, Kriteria 5).
 * Sends multiple simultaneous registration requests with identical phone number
 * using curl_multi to verify that database locking (GET_LOCK + UNIQUE constraint)
 * completely prevents race conditions.
 */

$baseUrl = 'http://127.0.0.1:8000/api/auth/register';
$phone = '0877' . random_int(10000000, 99999999);
$numRequests = 10;

echo "=======================================================\n";
echo "CONCURRENCY TEST: REGISTRATION RACE CONDITION TEST\n";
echo "Target Phone: $phone\n";
echo "Sending $numRequests parallel registration requests...\n";
echo "=======================================================\n";

$mh = curl_multi_init();
$handles = [];

for ($i = 1; $i <= $numRequests; $i++) {
    $ch = curl_init($baseUrl);
    $payload = json_encode([
        'name' => "Concurrent Racer",
        'phone' => $phone,
        'password' => 'secret123',
    ]);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);

    curl_multi_add_handle($mh, $ch);
    $handles[$i] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$statusCounts = [];
$responses = [];

foreach ($handles as $i => $ch) {
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $res = curl_multi_getcontent($ch);
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    $responses[] = [
        'request' => $i,
        'status' => $status,
        'body' => json_decode((string) $res, true),
    ];
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo "Results summary by HTTP Status Code:\n";
foreach ($statusCounts as $code => $count) {
    echo " - HTTP $code: $count requests\n";
}

$successCount = $statusCounts[201] ?? 0;
$conflictCount = $statusCounts[409] ?? 0;
$serverErrorCount = $statusCounts[500] ?? 0;

echo "-------------------------------------------------------\n";
if ($successCount === 1 && $conflictCount === ($numRequests - 1) && $serverErrorCount === 0) {
    echo "TEST PASSED: Exactly 1 registration succeeded (201 Created) and " . ($numRequests - 1) . " were cleanly rejected with 409 Conflict. Zero 500 errors.\n";
} else {
    echo "TEST FAILED: Unexpected distribution ($successCount success, $conflictCount conflict, $serverErrorCount errors)\n";
}
echo "=======================================================\n";

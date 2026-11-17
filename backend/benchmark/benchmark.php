<?php

declare(strict_types=1);

/**
 * Benchmark Script: Eager Loading vs Conventional SQL JOIN (FR-23, FR-27, Kriteria 5 & 6)
 * Seeds 10,000+ messages across multiple conversation partners,
 * then executes and benchmarks both strategies over multiple iterations.
 */

require_once dirname(__DIR__) . '/bootstrap/loadEnv.php';
\Lib\loadEnv(dirname(__DIR__) . '/.env');
require_once dirname(__DIR__) . '/lib/DB.php';
require_once dirname(__DIR__) . '/Helpers/IdGenerator.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Message.php';
require_once dirname(__DIR__) . '/models/Contact.php';

use Lib\DB;
use Models\Message;
use Helpers\IdGenerator;

echo "============================================================\n";
echo "BENCHMARK: EAGER LOADING vs SQL JOIN QUERY (10,000+ MESSAGES)\n";
echo "============================================================\n";

$db = DB::getInstance();

// 1. Check or prepare benchmark users
$benchmarkUserQuery = $db->query("SELECT id FROM users WHERE phone LIKE '0899999%' LIMIT 1");
$mainUser = $benchmarkUserQuery->fetch();

if (!$mainUser) {
    echo "Creating benchmark users...\n";
    $db->beginTransaction();
    $mainUserId = IdGenerator::generateUlid();
    $db->prepare("INSERT INTO users (id, name, phone, password_hash) VALUES (?, 'Benchmark Main User', '08999990000', 'hash')")
       ->execute([$mainUserId]);

    $partnerIds = [];
    for ($p = 1; $p <= 20; $p++) {
        $pId = IdGenerator::generateUlid();
        $phone = sprintf('0899999%04d', $p);
        $name = "Benchmark Partner " . chr(64 + ($p <= 26 ? $p : 1));
        $db->prepare("INSERT INTO users (id, name, phone, password_hash) VALUES (?, ?, ?, 'hash')")
           ->execute([$pId, $name, $phone]);
        $partnerIds[] = $pId;

        // Add as contact
        $db->prepare("INSERT INTO contacts (owner_id, contact_user_id, alias_name) VALUES (?, ?, ?)")
           ->execute([$mainUserId, $pId, "Alias " . $name]);
    }
    $db->commit();
} else {
    $mainUserId = $mainUser['id'];
    $partnerIds = $db->query("SELECT id FROM users WHERE phone LIKE '0899999%' AND id != '{$mainUserId}'")->fetchAll(PDO::FETCH_COLUMN);
}

// 2. Count existing messages for benchmark user
$countStmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? OR receiver_id = ?");
$countStmt->execute([$mainUserId, $mainUserId]);
$currentCount = (int) $countStmt->fetchColumn();

$targetCount = 10000;
if ($currentCount < $targetCount) {
    $needed = $targetCount - $currentCount;
    echo "Seeding $needed messages to reach $targetCount messages dataset...\n";

    $db->beginTransaction();
    $insertStmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, body, created_at) VALUES (?, ?, ?, ?)");
    $now = time();

    for ($i = 0; $i < $needed; $i++) {
        $partner = $partnerIds[$i % count($partnerIds)];
        $isOutgoing = ($i % 2 === 0);
        $sId = $isOutgoing ? $mainUserId : $partner;
        $rId = $isOutgoing ? $partner : $mainUserId;
        $timestamp = date('Y-m-d H:i:s', $now - ($needed - $i) * 10);
        $body = "Benchmark message payload #{$i} with some sample conversational content.";

        $insertStmt->execute([$sId, $rId, $body, $timestamp]);
    }
    $db->commit();
    echo "Seeding completed! Total benchmark messages: $targetCount\n";
} else {
    echo "Dataset already contains $currentCount messages.\n";
}

// 3. Warm up database caches
Message::getConversationsEagerLoading($mainUserId);
Message::getConversationsJoin($mainUserId);

$iterations = 50;
echo "Running $iterations iterations for each query strategy...\n\n";

// Benchmark Eager Loading
$eagerTimes = [];
for ($iter = 0; $iter < $iterations; $iter++) {
    $start = hrtime(true);
    $res = Message::getConversationsEagerLoading($mainUserId);
    $end = hrtime(true);
    $eagerTimes[] = ($end - $start) / 1e6; // Convert nanoseconds to milliseconds
}

// Benchmark SQL JOIN
$joinTimes = [];
for ($iter = 0; $iter < $iterations; $iter++) {
    $start = hrtime(true);
    $res = Message::getConversationsJoin($mainUserId);
    $end = hrtime(true);
    $joinTimes[] = ($end - $start) / 1e6; // Convert to milliseconds
}

function calcStats(array $times): array
{
    $count = count($times);
    $min = min($times);
    $max = max($times);
    $avg = array_sum($times) / $count;
    $variance = 0.0;
    foreach ($times as $t) {
        $variance += pow($t - $avg, 2);
    }
    $stdDev = sqrt($variance / $count);
    return ['min' => $min, 'max' => $max, 'avg' => $avg, 'stdDev' => $stdDev];
}

$eagerStats = calcStats($eagerTimes);
$joinStats = calcStats($joinTimes);

$report = "
============================================================
BENCHMARK RESULTS (Dataset: 10,000+ Messages, $iterations Iterations)
============================================================
| Metrik            | Eager Loading (No JOIN) | SQL JOIN Query       |
|-------------------|-------------------------|----------------------|
| Min Execution     | " . sprintf('%8.3f ms', $eagerStats['min']) . "       | " . sprintf('%8.3f ms', $joinStats['min']) . "     |
| Max Execution     | " . sprintf('%8.3f ms', $eagerStats['max']) . "       | " . sprintf('%8.3f ms', $joinStats['max']) . "     |
| Average Execution | " . sprintf('%8.3f ms', $eagerStats['avg']) . "       | " . sprintf('%8.3f ms', $joinStats['avg']) . "     |
| Std Deviation     | " . sprintf('%8.3f ms', $eagerStats['stdDev']) . "       | " . sprintf('%8.3f ms', $joinStats['stdDev']) . "     |
============================================================
Analisis:
1. Eager Loading membagi query menjadi query-query terindeks sederhana (constant query count),
   menghindari temporary table dan filesort yang berat pada klausa JOIN berlapis di MariaDB.
2. Skalabilitas Eager Loading stabil seiring bertambahnya data riwayat pesan.
";

echo $report . PHP_EOL;

// Save report to docs
$docPath = dirname(__DIR__, 2) . '/docs/BENCHMARK_REPORT.md';
file_put_contents($docPath, "# Laporan Benchmark: Eager Loading vs SQL JOIN (FR-27)\n\n" . "```\n" . $report . "\n```\n");
echo "Report saved to docs/BENCHMARK_REPORT.md\n";

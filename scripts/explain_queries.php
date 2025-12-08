<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

function runExplain(string $label, string $sql): void {
    echo "\n=== $label ===\n";
    $rows = DB::select("EXPLAIN (ANALYZE, BUFFERS, COSTS, VERBOSE) $sql");
    foreach ($rows as $row) {
        echo (array_values((array)$row)[0] ?? '') . "\n";
    }
}

// Parameters
$userId = 1;
$start = '2025-01-01';
$end   = '2025-12-31';
$week  = '2025-10-13';

// 1) User attendances in range, present only
$q1 = "SELECT id, user_id, date, status FROM attendances 
       WHERE user_id = $userId 
         AND date BETWEEN '$start' AND '$end' 
         AND status IN ('on_time','late')
       ORDER BY date";
runExplain('Attendances (range + present + order by date)', $q1);

// 2) Pending leave requests for a user
$q2 = "SELECT id, user_id, status, created_at FROM leave_requests 
       WHERE user_id = $userId AND status = 0 
       ORDER BY created_at DESC LIMIT 20";
runExplain('Leave Requests (pending by user, order by created_at desc)', $q2);

// 3) Weekly shifts for a user
$q3 = "SELECT id, user_id, week_start, day FROM shiftschedules 
       WHERE user_id = $userId AND week_start = '$week' 
       ORDER BY day";
runExplain('Shiftschedules (user + week_start, order by day)', $q3);



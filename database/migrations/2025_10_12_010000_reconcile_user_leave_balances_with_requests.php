<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure balances reflect approved requests already in DB (seeded or existing)
        // Subtract count of approved requests per type from the users' balances
        try {
            DB::statement("UPDATE users SET annual_leave_balance = GREATEST(0, annual_leave_balance - COALESCE(reqs.cnt,0)) FROM (
                SELECT user_id, COUNT(*) AS cnt FROM requests WHERE type='Annual Leave' AND status=1 GROUP BY user_id
            ) reqs WHERE reqs.user_id = users.id");
        } catch (\Throwable $e) {}
        try {
            DB::statement("UPDATE users SET sick_leave_balance = GREATEST(0, sick_leave_balance - COALESCE(reqs.cnt,0)) FROM (
                SELECT user_id, COUNT(*) AS cnt FROM requests WHERE type='Sick Leave' AND status=1 GROUP BY user_id
            ) reqs WHERE reqs.user_id = users.id");
        } catch (\Throwable $e) {}
        try {
            DB::statement("UPDATE users SET emergency_leave_balance = GREATEST(0, emergency_leave_balance - COALESCE(reqs.cnt,0)) FROM (
                SELECT user_id, COUNT(*) AS cnt FROM requests WHERE type='Emergency Leave' AND status=1 GROUP BY user_id
            ) reqs WHERE reqs.user_id = users.id");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // No-op: cannot safely reverse reconciliation without historical snapshot
    }
};



<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Recalculate users' leave balances by counting DAYS of approved requests
        // Uses PostgreSQL date arithmetic; assumes defaults 12/14/3 set earlier
        try {
            DB::statement("UPDATE users 
                SET annual_leave_balance = GREATEST(0, annual_leave_balance - COALESCE(used.days, 0))
                FROM (
                    SELECT user_id,
                           SUM(CASE WHEN end_date IS NULL THEN 1 ELSE (end_date - start_date + 1) END) AS days
                    FROM leave_requests
                    WHERE type = 'Annual Leave' AND status = 1
                    GROUP BY user_id
                ) used
                WHERE used.user_id = users.id");
        } catch (\Throwable $e) {}

        try {
            DB::statement("UPDATE users 
                SET sick_leave_balance = GREATEST(0, sick_leave_balance - COALESCE(used.days, 0))
                FROM (
                    SELECT user_id,
                           SUM(CASE WHEN end_date IS NULL THEN 1 ELSE (end_date - start_date + 1) END) AS days
                    FROM leave_requests
                    WHERE type = 'Sick Leave' AND status = 1
                    GROUP BY user_id
                ) used
                WHERE used.user_id = users.id");
        } catch (\Throwable $e) {}

        try {
            DB::statement("UPDATE users 
                SET emergency_leave_balance = GREATEST(0, emergency_leave_balance - COALESCE(used.days, 0))
                FROM (
                    SELECT user_id,
                           SUM(CASE WHEN end_date IS NULL THEN 1 ELSE (end_date - start_date + 1) END) AS days
                    FROM leave_requests
                    WHERE type = 'Emergency Leave' AND status = 1
                    GROUP BY user_id
                ) used
                WHERE used.user_id = users.id");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: cannot safely reverse without snapshot of prior balances
    }
};

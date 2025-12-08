<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Composite indexes for common queries
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_attendances_user_date_status ON attendances(user_id, date, status)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_leave_requests_user_status ON leave_requests(user_id, status)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_shiftschedules_user_week ON shiftschedules(user_id, week_start)'); } catch (\Throwable $e) {}

        // Partial indexes for frequent filters
        try { DB::statement("CREATE INDEX IF NOT EXISTS idx_leave_requests_pending ON leave_requests(user_id, created_at) WHERE status = 0"); } catch (\Throwable $e) {}
        try { DB::statement("CREATE INDEX IF NOT EXISTS idx_attendances_present ON attendances(user_id, date) WHERE status IN ('on_time','late')"); } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try { DB::statement('DROP INDEX IF EXISTS idx_attendances_user_date_status'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_leave_requests_user_status'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_shiftschedules_user_week'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_leave_requests_pending'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_attendances_present'); } catch (\Throwable $e) {}
    }
};

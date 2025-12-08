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
        // Ensure one employee per shift slot per day
        // Unique by (week_start, day, shift_type)
        try { DB::statement('DROP INDEX IF EXISTS idx_shiftschedules_unique_user_day_type'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_shiftschedules_unique_slot ON shiftschedules(week_start, day, shift_type)'); } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try { DB::statement('DROP INDEX IF EXISTS idx_shiftschedules_unique_slot'); } catch (\Throwable $e) {}
        // Optionally restore previous user-based uniqueness
        try { DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_shiftschedules_unique_user_day_type ON shiftschedules(user_id, day, shift_type)'); } catch (\Throwable $e) {}
    }
};

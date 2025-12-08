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
        // Enforce one attendance per user per shift
        try { DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS attendances_user_shift_unique ON attendances("userID", "shiftID")'); } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try { DB::statement('DROP INDEX IF EXISTS attendances_user_shift_unique'); } catch (\Throwable $e) {}
    }
};

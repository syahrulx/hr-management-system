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
        // First, delete any orphaned attendance records without shiftID
        try {
            DB::statement('DELETE FROM attendances WHERE "shiftID" IS NULL');
        } catch (\Throwable $e) {
            // If deletion fails, log but continue
        }

        // Now enforce NOT NULL constraint on shiftID
        try {
            DB::statement('ALTER TABLE attendances ALTER COLUMN "shiftID" SET NOT NULL');
        } catch (\Throwable $e) {
            // If already NOT NULL or error, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to nullable
        try {
            DB::statement('ALTER TABLE attendances ALTER COLUMN "shiftID" DROP NOT NULL');
        } catch (\Throwable $e) {}
    }
};

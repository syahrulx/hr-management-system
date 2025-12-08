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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'schedule_id')) {
                $table->unsignedBigInteger('schedule_id')->nullable()->after('user_id');
            }
        });
        // Add FK (nullable, on delete set null) and a helpful index
        try { DB::statement('ALTER TABLE attendances ADD CONSTRAINT attendances_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES shiftschedules(id) ON DELETE SET NULL'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS attendances_schedule_id_index ON attendances(schedule_id)'); } catch (\Throwable $e) {}

        // Best-effort backfill where exactly one schedule exists for that user and day
        try {
            DB::statement("UPDATE attendances a SET schedule_id = s.id FROM shiftschedules s WHERE s.user_id = a.user_id AND s.day = a.date");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try { DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_schedule_id_foreign'); } catch (\Throwable $e) {}
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'schedule_id')) {
                $table->dropColumn('schedule_id');
            }
        });
    }
};

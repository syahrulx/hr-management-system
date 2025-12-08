<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename schedules -> shiftschedules
        if (Schema::hasTable('schedules') && !Schema::hasTable('shiftschedules')) {
            Schema::rename('schedules', 'shiftschedules');
        }

        // Update tasks FK to reference shiftschedules
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                // Best-effort drop existing FK (tasks_schedule_id_foreign by default)
                try { $table->dropForeign(['schedule_id']); } catch (\Throwable $e) {}
                $table->foreign('schedule_id')->references('id')->on('shiftschedules')->onDelete('cascade');
            });
        }

        // Add useful columns and indexes to shiftschedules
        if (Schema::hasTable('shiftschedules')) {
            Schema::table('shiftschedules', function (Blueprint $table) {
                if (!Schema::hasColumn('shiftschedules', 'start_time')) {
                    $table->time('start_time')->nullable()->after('shift_type');
                }
                if (!Schema::hasColumn('shiftschedules', 'end_time')) {
                    $table->time('end_time')->nullable()->after('start_time');
                }
                // Indexes for performance
                if (!Schema::hasColumn('shiftschedules', 'day')) {
                    // nothing
                }
            });
            // Add indexes outside column callback to avoid duplicate index errors on reruns
            try { Schema::table('shiftschedules', function (Blueprint $table) { $table->index(['employee_id', 'day']); }); } catch (\Throwable $e) {}
            try { Schema::table('shiftschedules', function (Blueprint $table) { $table->index(['employee_id', 'day', 'shift_type']); }); } catch (\Throwable $e) {}
        }

        // Drop legacy shift tables if they exist
        if (Schema::hasTable('employee_shifts')) {
            Schema::drop('employee_shifts');
        }
        if (Schema::hasTable('shifts')) {
            Schema::drop('shifts');
        }
    }

    public function down(): void
    {
        // Recreate legacy tables minimal (optional). We'll just rename back for down.
        if (Schema::hasTable('shiftschedules') && !Schema::hasTable('schedules')) {
            Schema::rename('shiftschedules', 'schedules');
        }

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                try { $table->dropForeign(['schedule_id']); } catch (\Throwable $e) {}
                $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            });
        }
    }
};



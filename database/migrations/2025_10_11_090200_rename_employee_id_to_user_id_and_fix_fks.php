<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Attendances
        if (Schema::hasTable('attendances')) {
            // Drop by explicit names to avoid missing-constraint errors
            try { DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_employee_id_foreign'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_employee_id_date_unique'); } catch (\Throwable $e) {}

            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'employee_id') && !Schema::hasColumn('attendances', 'user_id')) {
                    $table->renameColumn('employee_id', 'user_id');
                }
            });

            // Create new constraints/indexes with explicit names
            // No need to drop user_id constraints if not present; just (re)create
            Schema::table('attendances', function (Blueprint $table) {
                try { $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique'); } catch (\Throwable $e) {}
                try { $table->foreign('user_id', 'attendances_user_id_foreign')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }

        // Requests
        if (Schema::hasTable('requests')) {
            try { DB::statement('ALTER TABLE requests DROP CONSTRAINT IF EXISTS requests_employee_id_foreign'); } catch (\Throwable $e) {}
            Schema::table('requests', function (Blueprint $table) {
                if (Schema::hasColumn('requests', 'employee_id') && !Schema::hasColumn('requests', 'user_id')) {
                    $table->renameColumn('employee_id', 'user_id');
                }
            });
            // No need to drop user_id FK if not present
            Schema::table('requests', function (Blueprint $table) {
                try { $table->foreign('user_id', 'requests_user_id_foreign')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }

        // Shift schedules
        if (Schema::hasTable('shiftschedules')) {
            try { DB::statement('ALTER TABLE shiftschedules DROP CONSTRAINT IF EXISTS shiftschedules_employee_id_foreign'); } catch (\Throwable $e) {}
            Schema::table('shiftschedules', function (Blueprint $table) {
                if (Schema::hasColumn('shiftschedules', 'employee_id') && !Schema::hasColumn('shiftschedules', 'user_id')) {
                    $table->renameColumn('employee_id', 'user_id');
                }
            });
            // No need to drop user_id FK if not present
            Schema::table('shiftschedules', function (Blueprint $table) {
                try { $table->foreign('user_id', 'shiftschedules_user_id_foreign')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
                try { $table->index(['user_id', 'day']); } catch (\Throwable $e) {}
                try { $table->index(['user_id', 'day', 'shift_type']); } catch (\Throwable $e) {}
            });
        }

        // Employee leaves
        if (Schema::hasTable('employee_leaves')) {
            try { DB::statement('ALTER TABLE employee_leaves DROP CONSTRAINT IF EXISTS employee_leaves_employee_id_foreign'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE employee_leaves DROP CONSTRAINT IF EXISTS employee_leaves_employee_id_leave_type_unique'); } catch (\Throwable $e) {}
            Schema::table('employee_leaves', function (Blueprint $table) {
                if (Schema::hasColumn('employee_leaves', 'employee_id') && !Schema::hasColumn('employee_leaves', 'user_id')) {
                    $table->renameColumn('employee_id', 'user_id');
                }
            });
            // No need to drop user_id constraints if not present
            Schema::table('employee_leaves', function (Blueprint $table) {
                try { $table->unique(['user_id', 'leave_type'], 'employee_leaves_user_id_leave_type_unique'); } catch (\Throwable $e) {}
                try { $table->foreign('user_id', 'employee_leaves_user_id_foreign')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        // Reverse of above: rename user_id back to employee_id and FKs to employees
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
                try { $table->dropUnique(['user_id', 'date']); } catch (\Throwable $e) {}
            });
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'user_id') && !Schema::hasColumn('attendances', 'employee_id')) {
                    $table->renameColumn('user_id', 'employee_id');
                }
            });
        }

        if (Schema::hasTable('requests')) {
            Schema::table('requests', function (Blueprint $table) {
                try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
                if (Schema::hasColumn('requests', 'user_id') && !Schema::hasColumn('requests', 'employee_id')) {
                    $table->renameColumn('user_id', 'employee_id');
                }
            });
        }

        if (Schema::hasTable('shiftschedules')) {
            Schema::table('shiftschedules', function (Blueprint $table) {
                try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
            });
            Schema::table('shiftschedules', function (Blueprint $table) {
                if (Schema::hasColumn('shiftschedules', 'user_id') && !Schema::hasColumn('shiftschedules', 'employee_id')) {
                    $table->renameColumn('user_id', 'employee_id');
                }
            });
        }

        if (Schema::hasTable('employee_leaves')) {
            Schema::table('employee_leaves', function (Blueprint $table) {
                try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
                try { $table->dropUnique(['user_id', 'leave_type']); } catch (\Throwable $e) {}
            });
            Schema::table('employee_leaves', function (Blueprint $table) {
                if (Schema::hasColumn('employee_leaves', 'user_id') && !Schema::hasColumn('employee_leaves', 'employee_id')) {
                    $table->renameColumn('user_id', 'employee_id');
                }
            });
        }
    }
};



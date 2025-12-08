<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'annual_leave_balance')) {
                $table->integer('annual_leave_balance')->default(0);
            }
            if (!Schema::hasColumn('users', 'sick_leave_balance')) {
                $table->integer('sick_leave_balance')->default(0);
            }
            if (!Schema::hasColumn('users', 'emergency_leave_balance')) {
                $table->integer('emergency_leave_balance')->default(0);
            }
        });

        // Set defaults for admin and employee roles
        try {
            DB::statement("UPDATE users SET annual_leave_balance = 12, sick_leave_balance = 14, emergency_leave_balance = 3 WHERE \"userRole\" IN ('admin','employee')");
        } catch (\Throwable $e) {}

        // Backfill from employee_leaves table if exists
        if (Schema::hasTable('employee_leaves')) {
            try {
                DB::statement("UPDATE users SET annual_leave_balance = el.balance FROM employee_leaves el WHERE el.user_id = users.id AND el.leave_type = 'Annual Leave'");
            } catch (\Throwable $e) {}
            try {
                DB::statement("UPDATE users SET sick_leave_balance = el.balance FROM employee_leaves el WHERE el.user_id = users.id AND el.leave_type = 'Sick Leave'");
            } catch (\Throwable $e) {}
            try {
                DB::statement("UPDATE users SET emergency_leave_balance = el.balance FROM employee_leaves el WHERE el.user_id = users.id AND el.leave_type = 'Emergency Leave'");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'annual_leave_balance')) {
                $table->dropColumn('annual_leave_balance');
            }
            if (Schema::hasColumn('users', 'sick_leave_balance')) {
                $table->dropColumn('sick_leave_balance');
            }
            if (Schema::hasColumn('users', 'emergency_leave_balance')) {
                $table->dropColumn('emergency_leave_balance');
            }
        });
    }
};



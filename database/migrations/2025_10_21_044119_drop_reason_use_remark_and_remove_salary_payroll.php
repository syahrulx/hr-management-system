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
        // leave_requests: rename reason -> remark, if both exist prefer remark and drop reason
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'reason')) {
                    if (!Schema::hasColumn('leave_requests', 'remark')) {
                        $table->text('remark')->nullable()->after('status');
                    }
                }
            });
            try { DB::statement('UPDATE leave_requests SET remark = COALESCE(remark, reason)'); } catch (\Throwable $e) {}
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'reason')) {
                    $table->dropColumn('reason');
                }
            });
        }

        // users: remove payroll_day and salary if present
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'payroll_day')) {
                    $table->dropColumn('payroll_day');
                }
                if (Schema::hasColumn('users', 'salary')) {
                    $table->dropColumn('salary');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('leave_requests', 'reason')) {
                    $table->text('reason')->nullable();
                }
            });
            try { DB::statement('UPDATE leave_requests SET reason = COALESCE(reason, remark)'); } catch (\Throwable $e) {}
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'remark')) {
                    $table->dropColumn('remark');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'payroll_day')) {
                    $table->unsignedTinyInteger('payroll_day')->default(1);
                }
                if (!Schema::hasColumn('users', 'salary')) {
                    $table->decimal('salary', 10, 2)->nullable();
                }
            });
        }
    }
};

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
        // USERS: rename & drop
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'id') && !Schema::hasColumn('users', 'userID')) {
                    $table->renameColumn('id', 'userID');
                }
                if (Schema::hasColumn('users', 'national_id') && !Schema::hasColumn('users', 'icNumber')) {
                    $table->renameColumn('national_id', 'icNumber');
                }
                if (Schema::hasColumn('users', 'hired_on') && !Schema::hasColumn('users', 'hiredOn')) {
                    $table->renameColumn('hired_on', 'hiredOn');
                }
                if (Schema::hasColumn('users', 'annual_leave_balance') && !Schema::hasColumn('users', 'annualLeaveBalance')) {
                    $table->renameColumn('annual_leave_balance', 'annualLeaveBalance');
                }
                if (Schema::hasColumn('users', 'sick_leave_balance') && !Schema::hasColumn('users', 'sickLeaveBalance')) {
                    $table->renameColumn('sick_leave_balance', 'sickLeaveBalance');
                }
                if (Schema::hasColumn('users', 'emergency_leave_balance') && !Schema::hasColumn('users', 'emergencyLeaveBalance')) {
                    $table->renameColumn('emergency_leave_balance', 'emergencyLeaveBalance');
                }
                // Drops
                foreach (['created_at','updated_at','deleted_at','email_verified_at','remember_token','bank_acc_no'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // ATTENDANCES: rename & drop
        if (Schema::hasTable('attendances')) {
            // drop unique on (user_id,date) if exists to allow date drop
            try { DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_user_id_date_unique'); } catch (\Throwable $e) {}
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'id') && !Schema::hasColumn('attendances', 'attendanceID')) {
                    $table->renameColumn('id', 'attendanceID');
                }
                if (Schema::hasColumn('attendances', 'user_id') && !Schema::hasColumn('attendances', 'userID')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('attendances', 'schedule_id') && !Schema::hasColumn('attendances', 'shiftID')) {
                    $table->renameColumn('schedule_id', 'shiftID');
                }
                if (Schema::hasColumn('attendances', 'sign_in_time') && !Schema::hasColumn('attendances', 'clockInTime')) {
                    $table->renameColumn('sign_in_time', 'clockInTime');
                }
                if (Schema::hasColumn('attendances', 'sign_off_time') && !Schema::hasColumn('attendances', 'clockOutTime')) {
                    $table->renameColumn('sign_off_time', 'clockOutTime');
                }
                foreach (['date','notes','is_manually_filled','created_at','updated_at','deleted_at'] as $col) {
                    if (Schema::hasColumn('attendances', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // SHIFTSCHEDULES: rename & drop
        if (Schema::hasTable('shiftschedules')) {
            Schema::table('shiftschedules', function (Blueprint $table) {
                if (Schema::hasColumn('shiftschedules', 'id') && !Schema::hasColumn('shiftschedules', 'shiftID')) {
                    $table->renameColumn('id', 'shiftID');
                }
                if (Schema::hasColumn('shiftschedules', 'user_id') && !Schema::hasColumn('shiftschedules', 'userID')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('shiftschedules', 'day') && !Schema::hasColumn('shiftschedules', 'shiftDate')) {
                    $table->renameColumn('day', 'shiftDate');
                }
                if (Schema::hasColumn('shiftschedules', 'shift_type') && !Schema::hasColumn('shiftschedules', 'shiftType')) {
                    $table->renameColumn('shift_type', 'shiftType');
                }
                foreach (['week_start','submitted','created_at','updated_at','deleted_at'] as $col) {
                    if (Schema::hasColumn('shiftschedules', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // LEAVE_REQUESTS: rename & drop
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'id') && !Schema::hasColumn('leave_requests', 'requestID')) {
                    $table->renameColumn('id', 'requestID');
                }
                if (Schema::hasColumn('leave_requests', 'user_id') && !Schema::hasColumn('leave_requests', 'userID')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('leave_requests', 'start_date') && !Schema::hasColumn('leave_requests', 'startDate')) {
                    $table->renameColumn('start_date', 'startDate');
                }
                if (Schema::hasColumn('leave_requests', 'end_date') && !Schema::hasColumn('leave_requests', 'endDate')) {
                    $table->renameColumn('end_date', 'endDate');
                }
                if (Schema::hasColumn('leave_requests', 'message') && !Schema::hasColumn('leave_requests', 'reason')) {
                    $table->renameColumn('message', 'reason');
                }
                if (Schema::hasColumn('leave_requests', 'admin_response') && !Schema::hasColumn('leave_requests', 'remark')) {
                    $table->renameColumn('admin_response', 'remark');
                }
                foreach (['created_at','updated_at','deleted_at'] as $col) {
                    if (Schema::hasColumn('leave_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This down only restores renamed columns where safe; dropped columns are not restored
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'userID') && !Schema::hasColumn('users', 'id')) {
                    $table->renameColumn('userID', 'id');
                }
                if (Schema::hasColumn('users', 'icNumber') && !Schema::hasColumn('users', 'national_id')) {
                    $table->renameColumn('icNumber', 'national_id');
                }
                if (Schema::hasColumn('users', 'hiredOn') && !Schema::hasColumn('users', 'hired_on')) {
                    $table->renameColumn('hiredOn', 'hired_on');
                }
                if (Schema::hasColumn('users', 'annualLeaveBalance') && !Schema::hasColumn('users', 'annual_leave_balance')) {
                    $table->renameColumn('annualLeaveBalance', 'annual_leave_balance');
                }
                if (Schema::hasColumn('users', 'sickLeaveBalance') && !Schema::hasColumn('users', 'sick_leave_balance')) {
                    $table->renameColumn('sickLeaveBalance', 'sick_leave_balance');
                }
                if (Schema::hasColumn('users', 'emergencyLeaveBalance') && !Schema::hasColumn('users', 'emergency_leave_balance')) {
                    $table->renameColumn('emergencyLeaveBalance', 'emergency_leave_balance');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'attendanceID') && !Schema::hasColumn('attendances', 'id')) {
                    $table->renameColumn('attendanceID', 'id');
                }
                if (Schema::hasColumn('attendances', 'userID') && !Schema::hasColumn('attendances', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                if (Schema::hasColumn('attendances', 'shiftID') && !Schema::hasColumn('attendances', 'schedule_id')) {
                    $table->renameColumn('shiftID', 'schedule_id');
                }
                if (Schema::hasColumn('attendances', 'clockInTime') && !Schema::hasColumn('attendances', 'sign_in_time')) {
                    $table->renameColumn('clockInTime', 'sign_in_time');
                }
                if (Schema::hasColumn('attendances', 'clockOutTime') && !Schema::hasColumn('attendances', 'sign_off_time')) {
                    $table->renameColumn('clockOutTime', 'sign_off_time');
                }
            });
        }

        if (Schema::hasTable('shiftschedules')) {
            Schema::table('shiftschedules', function (Blueprint $table) {
                if (Schema::hasColumn('shiftschedules', 'shiftID') && !Schema::hasColumn('shiftschedules', 'id')) {
                    $table->renameColumn('shiftID', 'id');
                }
                if (Schema::hasColumn('shiftschedules', 'userID') && !Schema::hasColumn('shiftschedules', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                if (Schema::hasColumn('shiftschedules', 'shiftDate') && !Schema::hasColumn('shiftschedules', 'day')) {
                    $table->renameColumn('shiftDate', 'day');
                }
                if (Schema::hasColumn('shiftschedules', 'shiftType') && !Schema::hasColumn('shiftschedules', 'shift_type')) {
                    $table->renameColumn('shiftType', 'shift_type');
                }
            });
        }

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'requestID') && !Schema::hasColumn('leave_requests', 'id')) {
                    $table->renameColumn('requestID', 'id');
                }
                if (Schema::hasColumn('leave_requests', 'userID') && !Schema::hasColumn('leave_requests', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                if (Schema::hasColumn('leave_requests', 'startDate') && !Schema::hasColumn('leave_requests', 'start_date')) {
                    $table->renameColumn('startDate', 'start_date');
                }
                if (Schema::hasColumn('leave_requests', 'endDate') && !Schema::hasColumn('leave_requests', 'end_date')) {
                    $table->renameColumn('endDate', 'end_date');
                }
                if (Schema::hasColumn('leave_requests', 'reason') && !Schema::hasColumn('leave_requests', 'message')) {
                    $table->renameColumn('reason', 'message');
                }
                if (Schema::hasColumn('leave_requests', 'remark') && !Schema::hasColumn('leave_requests', 'admin_response')) {
                    $table->renameColumn('remark', 'admin_response');
                }
            });
        }
    }
};

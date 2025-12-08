<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL: This migration converts all camelCase column names to snake_case
     * This is a MAJOR schema change that affects the entire application
     */
    public function up(): void
    {
        // STEP 1: USERS TABLE
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Rename primary key
                if (Schema::hasColumn('users', 'userID') && !Schema::hasColumn('users', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                
                // Rename other columns
                if (Schema::hasColumn('users', 'icNumber') && !Schema::hasColumn('users', 'ic_number')) {
                    $table->renameColumn('icNumber', 'ic_number');
                }
                if (Schema::hasColumn('users', 'hiredOn') && !Schema::hasColumn('users', 'hired_on')) {
                    $table->renameColumn('hiredOn', 'hired_on');
                }
                if (Schema::hasColumn('users', 'userRole') && !Schema::hasColumn('users', 'user_role')) {
                    $table->renameColumn('userRole', 'user_role');
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

        // STEP 2: RENAME SHIFTSCHEDULES TABLE TO SHIFT_SCHEDULES
        if (Schema::hasTable('shiftschedules') && !Schema::hasTable('shift_schedules')) {
            Schema::rename('shiftschedules', 'shift_schedules');
        }

        // STEP 3: SHIFT_SCHEDULES TABLE (after rename)
        if (Schema::hasTable('shift_schedules')) {
            Schema::table('shift_schedules', function (Blueprint $table) {
                // Rename primary key
                if (Schema::hasColumn('shift_schedules', 'shiftID') && !Schema::hasColumn('shift_schedules', 'shift_id')) {
                    $table->renameColumn('shiftID', 'shift_id');
                }
                
                // Rename foreign key
                if (Schema::hasColumn('shift_schedules', 'userID') && !Schema::hasColumn('shift_schedules', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                
                // Rename other columns
                if (Schema::hasColumn('shift_schedules', 'shiftDate') && !Schema::hasColumn('shift_schedules', 'shift_date')) {
                    $table->renameColumn('shiftDate', 'shift_date');
                }
                if (Schema::hasColumn('shift_schedules', 'shiftType') && !Schema::hasColumn('shift_schedules', 'shift_type')) {
                    $table->renameColumn('shiftType', 'shift_type');
                }
            });
        }

        // STEP 4: ATTENDANCES TABLE
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                // Rename primary key
                if (Schema::hasColumn('attendances', 'attendanceID') && !Schema::hasColumn('attendances', 'attendance_id')) {
                    $table->renameColumn('attendanceID', 'attendance_id');
                }
                
                // Rename foreign keys
                if (Schema::hasColumn('attendances', 'userID') && !Schema::hasColumn('attendances', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                if (Schema::hasColumn('attendances', 'shiftID') && !Schema::hasColumn('attendances', 'shift_id')) {
                    $table->renameColumn('shiftID', 'shift_id');
                }
                
                // Rename other columns
                if (Schema::hasColumn('attendances', 'clockInTime') && !Schema::hasColumn('attendances', 'clock_in_time')) {
                    $table->renameColumn('clockInTime', 'clock_in_time');
                }
                if (Schema::hasColumn('attendances', 'clockOutTime') && !Schema::hasColumn('attendances', 'clock_out_time')) {
                    $table->renameColumn('clockOutTime', 'clock_out_time');
                }
            });
        }

        // STEP 5: LEAVE_REQUESTS TABLE
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                // Rename primary key
                if (Schema::hasColumn('leave_requests', 'requestID') && !Schema::hasColumn('leave_requests', 'request_id')) {
                    $table->renameColumn('requestID', 'request_id');
                }
                
                // Rename foreign key
                if (Schema::hasColumn('leave_requests', 'userID') && !Schema::hasColumn('leave_requests', 'user_id')) {
                    $table->renameColumn('userID', 'user_id');
                }
                
                // Rename date columns
                if (Schema::hasColumn('leave_requests', 'startDate') && !Schema::hasColumn('leave_requests', 'start_date')) {
                    $table->renameColumn('startDate', 'start_date');
                }
                if (Schema::hasColumn('leave_requests', 'endDate') && !Schema::hasColumn('leave_requests', 'end_date')) {
                    $table->renameColumn('endDate', 'end_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse all changes (convert back to camelCase)
        
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'request_id')) {
                    $table->renameColumn('request_id', 'requestID');
                }
                if (Schema::hasColumn('leave_requests', 'user_id')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('leave_requests', 'start_date')) {
                    $table->renameColumn('start_date', 'startDate');
                }
                if (Schema::hasColumn('leave_requests', 'end_date')) {
                    $table->renameColumn('end_date', 'endDate');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'attendance_id')) {
                    $table->renameColumn('attendance_id', 'attendanceID');
                }
                if (Schema::hasColumn('attendances', 'user_id')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('attendances', 'shift_id')) {
                    $table->renameColumn('shift_id', 'shiftID');
                }
                if (Schema::hasColumn('attendances', 'clock_in_time')) {
                    $table->renameColumn('clock_in_time', 'clockInTime');
                }
                if (Schema::hasColumn('attendances', 'clock_out_time')) {
                    $table->renameColumn('clock_out_time', 'clockOutTime');
                }
            });
        }

        if (Schema::hasTable('shift_schedules')) {
            Schema::table('shift_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('shift_schedules', 'shift_id')) {
                    $table->renameColumn('shift_id', 'shiftID');
                }
                if (Schema::hasColumn('shift_schedules', 'user_id')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('shift_schedules', 'shift_date')) {
                    $table->renameColumn('shift_date', 'shiftDate');
                }
                if (Schema::hasColumn('shift_schedules', 'shift_type')) {
                    $table->renameColumn('shift_type', 'shiftType');
                }
            });
            
            Schema::rename('shift_schedules', 'shiftschedules');
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'user_id')) {
                    $table->renameColumn('user_id', 'userID');
                }
                if (Schema::hasColumn('users', 'ic_number')) {
                    $table->renameColumn('ic_number', 'icNumber');
                }
                if (Schema::hasColumn('users', 'hired_on')) {
                    $table->renameColumn('hired_on', 'hiredOn');
                }
                if (Schema::hasColumn('users', 'user_role')) {
                    $table->renameColumn('user_role', 'userRole');
                }
                if (Schema::hasColumn('users', 'annual_leave_balance')) {
                    $table->renameColumn('annual_leave_balance', 'annualLeaveBalance');
                }
                if (Schema::hasColumn('users', 'sick_leave_balance')) {
                    $table->renameColumn('sick_leave_balance', 'sickLeaveBalance');
                }
                if (Schema::hasColumn('users', 'emergency_leave_balance')) {
                    $table->renameColumn('emergency_leave_balance', 'emergencyLeaveBalance');
                }
            });
        }
    }
};


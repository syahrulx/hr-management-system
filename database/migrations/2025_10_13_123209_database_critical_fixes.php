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
        // 1) Drop duplicate/legacy indexes and FKs on shiftschedules
        try { DB::statement('DROP INDEX IF EXISTS shiftschedules_employee_id_day_index'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS shiftschedules_employee_id_day_shift_type_index'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE shiftschedules DROP CONSTRAINT IF EXISTS schedules_employee_id_foreign'); } catch (\Throwable $e) {}

        // 2) Add missing indexes for performance
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_leave_requests_user_id ON leave_requests(user_id)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_leave_requests_status ON leave_requests(status)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_leave_requests_type ON leave_requests(type)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_attendances_date ON attendances(date)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX IF NOT EXISTS idx_attendances_status ON attendances(status)'); } catch (\Throwable $e) {}

        // 3) Prevent duplicate schedules
        try { DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_shiftschedules_unique_user_day_type ON shiftschedules(user_id, day, shift_type)'); } catch (\Throwable $e) {}

        // 4) Add CHECK constraints for data integrity
        try { DB::statement('ALTER TABLE users ADD CONSTRAINT chk_annual_leave_balance CHECK (annual_leave_balance >= 0)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE users ADD CONSTRAINT chk_sick_leave_balance CHECK (sick_leave_balance >= 0)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE users ADD CONSTRAINT chk_emergency_leave_balance CHECK (emergency_leave_balance >= 0)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE users ADD CONSTRAINT chk_payroll_day CHECK (payroll_day BETWEEN 1 AND 31)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE leave_requests ADD CONSTRAINT chk_status CHECK (status IN (0, 1, 2))'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE leave_requests ADD CONSTRAINT chk_leave_dates CHECK (end_date IS NULL OR end_date >= start_date)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE shiftschedules ADD CONSTRAINT chk_shift_times CHECK (start_time IS NULL OR end_time IS NULL OR start_time < end_time)'); } catch (\Throwable $e) {}

        // 5) Rename legacy index names for consistency
        try { DB::statement('ALTER INDEX employees_email_unique RENAME TO users_email_unique'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER INDEX employees_national_id_unique RENAME TO users_national_id_unique'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER INDEX employees_phone_unique RENAME TO users_phone_unique'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER INDEX employees_pkey RENAME TO users_pkey'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER INDEX requests_pkey RENAME TO leave_requests_pkey'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER INDEX schedules_pkey RENAME TO shiftschedules_pkey'); } catch (\Throwable $e) {}

        // 6) Rename sequences created before table renames
        try { DB::statement('ALTER SEQUENCE employees_id_seq RENAME TO users_id_seq'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER SEQUENCE requests_id_seq RENAME TO leave_requests_id_seq'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER SEQUENCE schedules_id_seq RENAME TO shiftschedules_id_seq'); } catch (\Throwable $e) {}

        // 7) Rename FK constraints to clear names
        try { DB::statement('ALTER TABLE attendances RENAME CONSTRAINT attendances_user_id_foreign TO fk_attendances_user_id'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE leave_requests RENAME CONSTRAINT requests_user_id_foreign TO fk_leave_requests_user_id'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE shiftschedules RENAME CONSTRAINT shiftschedules_user_id_foreign TO fk_shiftschedules_user_id'); } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Best-effort rollback for non-destructive changes
        try { DB::statement('DROP INDEX IF EXISTS idx_leave_requests_user_id'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_leave_requests_status'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_leave_requests_type'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_attendances_date'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_attendances_status'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX IF EXISTS idx_shiftschedules_unique_user_day_type'); } catch (\Throwable $e) {}

        try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_annual_leave_balance'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_sick_leave_balance'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_emergency_leave_balance'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_payroll_day'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS chk_status'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS chk_leave_dates'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE shiftschedules DROP CONSTRAINT IF EXISTS chk_shift_times'); } catch (\Throwable $e) {}
    }
};

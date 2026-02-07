<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');

        // 1. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();

            $table->string('password');
            $table->string('user_role');
            $table->string('ic_number')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('hired_on')->nullable();

            // Leave Balances
            $table->integer('annual_leave_balance')->default(14);
            $table->integer('sick_leave_balance')->default(14);
            $table->integer('emergency_leave_balance')->default(7);



        });

        // 2. Password Reset Tokens (Standard Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Failed Jobs (Standard Laravel)
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 4. Personal Access Tokens (Standard Laravel - Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 5. Shift Schedules
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id('shift_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->date('shift_date');
            $table->string('shift_type'); // 'morning', 'evening', 'office'
        });

        // 6. Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shift_schedules', 'shift_id')->onDelete('set null');
            $table->string('status')->default('absent'); // 'present', 'late', 'absent', 'on_time'
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
        });

        // 7. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('type'); // 'Annual Leave', etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('status')->default(0); // 0: Pending, 1: Approved, 2: Rejected
            $table->text('remark')->nullable();
            $table->longText('support_doc')->nullable(); // Base64 encoded file
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

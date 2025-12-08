<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove unused columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['normalized_name']);
        });

        // Remove unused columns from leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['is_seen']);
        });

        // Remove unused columns from shiftschedules table
        Schema::table('shiftschedules', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('normalized_name')->nullable();
        });

        // Restore columns to leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->boolean('is_seen')->default(false);
        });

        // Restore columns to shiftschedules table
        Schema::table('shiftschedules', function (Blueprint $table) {
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }
};
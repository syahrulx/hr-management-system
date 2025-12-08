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
        // Drop employee_leaves table as leave balances are now in users table
        // (annual_leave_balance, sick_leave_balance, emergency_leave_balance columns)
        Schema::dropIfExists('employee_leaves');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate employee_leaves table if migration is rolled back
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('leave_type');
            $table->integer('balance')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'leave_type']);
        });
    }
};


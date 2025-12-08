<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'payroll_day')) {
                $table->unsignedTinyInteger('payroll_day')->default(1);
            }
            if (!Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'payroll_day')) {
                $table->dropColumn('payroll_day');
            }
            if (Schema::hasColumn('users', 'salary')) {
                $table->dropColumn('salary');
            }
        });
    }
};



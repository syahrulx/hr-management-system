<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && !Schema::hasTable('users')) {
            Schema::rename('employees', 'users');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && !Schema::hasTable('employees')) {
            Schema::rename('users', 'employees');
        }
    }
};



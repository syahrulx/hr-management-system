<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('shiftschedules') ? 'shiftschedules' : 'schedules';
        Schema::table($tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($table->getTable(), 'submitted')) {
                $table->boolean('submitted')->default(false)->after('day');
            }
        });
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('shiftschedules') ? 'shiftschedules' : 'schedules';
        Schema::table($tableName, function (Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'submitted')) {
                $table->dropColumn('submitted');
            }
        });
    }
}; 
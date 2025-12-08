<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks')) {
            Schema::drop('tasks');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();

                $table->foreign('schedule_id')->references('id')->on('shiftschedules')->onDelete('cascade');
            });
        }
    }
};



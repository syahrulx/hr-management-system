<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop Spatie Permission tables if they still exist
        try { DB::statement('DROP TABLE IF EXISTS role_has_permissions CASCADE'); } catch (\Throwable $e) {}
        try { DB::statement('DROP TABLE IF EXISTS model_has_roles CASCADE'); } catch (\Throwable $e) {}
        try { DB::statement('DROP TABLE IF EXISTS model_has_permissions CASCADE'); } catch (\Throwable $e) {}
        try { DB::statement('DROP TABLE IF EXISTS roles CASCADE'); } catch (\Throwable $e) {}
        try { DB::statement('DROP TABLE IF EXISTS permissions CASCADE'); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // no-op (we're intentionally removing legacy tables)
    }
};



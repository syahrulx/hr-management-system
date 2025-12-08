<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // If spatie tables exist, migrate first matching role name into users.userRole
        try {
            $hasModelRoles = DB::getSchemaBuilder()->hasTable('model_has_roles');
            $hasRoles = DB::getSchemaBuilder()->hasTable('roles');
            if ($hasModelRoles && $hasRoles) {
                $map = DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_type', 'App\\Models\\Employee')
                    ->select('model_has_roles.model_id as user_id', 'roles.name as role')
                    ->get();
                foreach ($map as $row) {
                    DB::table('users')->where('id', $row->user_id)->update(['userRole' => $row->role]);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        // no-op
    }
};



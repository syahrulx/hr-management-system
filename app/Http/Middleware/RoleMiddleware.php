<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roles)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        $allowed = array_map('trim', explode('|', $roles));
        if (!in_array($user->user_role ?? '', $allowed, true)) {
            abort(403);
        }
        return $next($request);
    }
}



<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // your User has a `role` column: manager | staff | customer
        if (!in_array($user->role, $roles, true)) {
            abort(403, 'You are not allowed to access this page.');
        }
        return $next($request);
    }
}

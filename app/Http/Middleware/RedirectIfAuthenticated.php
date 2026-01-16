<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = $guards ?: [null];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Already logged in → send to role dashboard
                $role = Auth::user()->role ?? 'customer';
                return match ($role) {
                    'manager'  => redirect()->route('manager.dashboard'),
                    'staff'    => redirect()->route('staff.dashboard'),
                    default    => redirect()->route('customer.dashboard'),
                };
            }
        }
        return $next($request);
    }
}

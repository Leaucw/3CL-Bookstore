<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // If unauthenticated on web, send to home (or a login chooser)
        return $request->expectsJson() ? null : route('home');
    }
}

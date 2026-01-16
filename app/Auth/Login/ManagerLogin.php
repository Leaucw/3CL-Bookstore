<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Auth\Login;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ManagerLogin implements LoginService
{
    public function attempt(array $c): string
    {
        if (!Auth::attempt([
            'username' => $c['username'] ?? '',
            'password' => $c['password'] ?? '',
        ])) {
            throw ValidationException::withMessages(['username' => 'Invalid manager credentials.']);
        }

        if (Auth::user()->role !== 'manager') {
            Auth::logout();
            throw ValidationException::withMessages(['username' => 'Not a manager account.']);
        }

        return 'manager.dashboard';
    }
}

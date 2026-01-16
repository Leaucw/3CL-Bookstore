<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Auth\Login;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StaffLogin implements LoginService
{
    public function attempt(array $c): string
    {
        if (!Auth::attempt([
            'username' => $c['username'] ?? '',
            'password' => $c['password'] ?? '',
        ])) {
            throw ValidationException::withMessages(['username' => 'Invalid staff credentials.']);
        }

        if (Auth::user()->role !== 'staff') {
            Auth::logout();
            throw ValidationException::withMessages(['username' => 'Not a staff account.']);
        }

        return 'staff.dashboard';
    }
}

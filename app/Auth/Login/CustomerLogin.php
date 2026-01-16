<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Auth\Login;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerLogin implements LoginService
{
    public function attempt(array $c): string
    {
        if (!Auth::attempt([
            'email'    => $c['email']    ?? '',
            'password' => $c['password'] ?? '',
        ])) {
            throw ValidationException::withMessages(['email' => 'Invalid customer credentials.']);
        }

        if (Auth::user()->role !== 'customer') {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Not a customer account.']);
        }

        return 'customer.dashboard';
    }
}

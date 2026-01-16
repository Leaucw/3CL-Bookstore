<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /** Show customer registration form */
    public function show()
    {
        return view('auth.register');
    }

    /** Handle customer registration (defaults role=customer) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_num', 'min:4', 'max:30', 'unique:users,username'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create($data + ['role' => 'customer']); // password hashed via mutator or cast
        Auth::login($user);

        return redirect()
            ->route('customer.dashboard')
            ->with('flash.success', 'Registration successful! Hello, ' . $user->name . '.');
    }
}

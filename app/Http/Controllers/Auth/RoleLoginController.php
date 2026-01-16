<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Auth;

use App\Auth\Login\LoginFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleLoginController extends Controller
{
    /** Show login forms (3 separate pages) */
    public function showManager()  { return view('auth.login-manager'); }
    public function showStaff()    { return view('auth.login-staff'); }
    public function showCustomer() { return view('auth.login-customer'); }

    /** Handle login using Factory Method (manager|staff|customer) */
    public function login(Request $request, string $type)
    {
        $service = LoginFactory::make($type);

        // Will throw ValidationException on invalid creds/role mismatch
        $redirectRouteName = $service->attempt($request->all());

        // session harden
        $request->session()->regenerate();

        // success toast
        return redirect()
            ->route($redirectRouteName)
            ->with('flash.success', 'Login successful. Welcome back, ' . Auth::user()->name . '!');
    }

    /** Handle logout */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('flash.info', 'You have been logged out.');
    }
}

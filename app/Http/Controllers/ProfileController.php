<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /** Show the profile form (manager has no profile features). */
    public function edit(Request $request)
    {
        $user = $request->user();

        // Managers: no profile page
        if ($user->role === 'manager') {
            return redirect()
                ->route('manager.dashboard')
                ->with('flash.info', 'Manager accounts do not have profile settings.');
        }

        // Build list of editable fields for the view
        $editable = $user->role === 'customer'
            ? ['name','username','email','phone','address','avatar','password'] // customer: everything
            : ['username','phone','address','avatar','password'];               // staff: no name/email

        return view('profile.edit', compact('user','editable'));
    }

    /** Update profile with role-specific rules + avatar removal. */
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            return redirect()
                ->route('manager.dashboard')
                ->with('flash.info', 'Manager accounts do not have profile settings.');
        }

        if ($user->role === 'customer') {
            $rules = [
                'name'     => ['required','string','max:255'],
                'username' => ['required','alpha_num','min:4','max:30', Rule::unique('users','username')->ignore($user->id)],
                'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
                'phone'    => ['nullable','string','max:50'],
                'address'  => ['nullable','string','max:255'],
                'avatar'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
                'remove_avatar' => ['sometimes','boolean'],
                'password' => ['nullable','confirmed','min:8'],
            ];
            $allowed = ['name','username','email','phone','address']; // avatar/password handled below
        } else { // staff
            $rules = [
                'username' => ['required','alpha_num','min:4','max:30', Rule::unique('users','username')->ignore($user->id)],
                'phone'    => ['nullable','string','max:50'],
                'address'  => ['nullable','string','max:255'],
                'avatar'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
                'remove_avatar' => ['sometimes','boolean'],
                'password' => ['nullable','confirmed','min:8'],
            ];
            $allowed = ['username','phone','address'];
        }

        $data = $request->validate($rules);

        // Keep only allowed fields to prevent tampering
        $payload = array_intersect_key($data, array_flip($allowed));

        // Password (only if provided) — rely on model cast/mutator for hashing
        if ($request->filled('password')) {
            $user->password = $data['password'];
        }

        // Remove avatar if requested
        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $payload['avatar_path'] = null;
        }

        // Upload avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $payload['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($payload);

        return back()->with('flash.success', 'Profile updated.');
    }
}

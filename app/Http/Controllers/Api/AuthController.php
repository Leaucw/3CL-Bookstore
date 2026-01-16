<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * body: { email, password, role?, device_name? }
     * returns: { token, token_type, user }
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'role'        => ['nullable', 'in:manager,staff,customer'],
            'device_name' => ['nullable', 'string', 'max:60'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // If caller specifies a role, enforce it.
        if (!empty($data['role']) && $user->role !== $data['role']) {
            return response()->json(['message' => 'Role mismatch for this account.'], 403);
        }

        $device = $data['device_name'] ?? 'api';
        $token  = $user->createToken($device)->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user->only(['id','name','username','email','role','phone','address','points','created_at']),
        ]);
    }

    /**
     * POST /api/v1/auth/register
     * Customer-only registration.
     * body: { name, username, email, password, password_confirmation, phone?, address? }
     * returns: { token, token_type, user }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required','string','max:100'],
            'username'              => ['required','alpha_num','min:4','max:30','unique:users,username'],
            'email'                 => ['required','email','max:255','unique:users,email'],
            'password'              => ['required','confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
            'phone'                 => ['nullable','string','max:20'],
            'address'               => ['nullable','string','max:255'],
        ]);

        $user = User::create($data + [
            'role'   => 'customer',
            'points' => 0,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user->only(['id','name','username','email','role','phone','address','points','created_at']),
        ], 201);
    }

    /**
     * POST /api/v1/auth/logout   (auth:sanctum)
     * Revokes only the current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * GET /api/v1/auth/me   (auth:sanctum)
     */
    public function me(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'user' => $u->only(['id','name','username','email','role','phone','address','points','created_at']),
        ]);
    }
}

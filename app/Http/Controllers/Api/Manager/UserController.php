<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /api/v1/manager/users
    public function index(Request $request)
    {
        $q = User::query(); // default excludes soft-deleted

        // Optional filters (kept):
        if ($role = $request->query('role')) {
            $q->where('role', $role); // 'manager'|'staff'|'customer'
        }

        if ($search = trim((string)$request->query('search', ''))) {
            $q->where(function ($x) use ($search) {
                $x->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Sorting (allow only safe columns)
        $sort = in_array($request->query('sort'), ['id','name','email','created_at'], true)
            ? $request->query('sort')
            : 'id';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $perPage = min(max((int)$request->query('perPage', 12), 1), 100);

        return $q->orderBy($sort, $dir)
                 ->simplePaginate($perPage)
                 ->appends($request->query())    // keep query string
                 ->through(function (User $u) {  // shape output
                     return [
                         'id'       => $u->id,
                         'name'     => $u->name,
                         'username' => $u->username,
                         'email'    => $u->email,
                         'role'     => $u->role,
                         'phone'    => $u->phone,
                         'address'  => $u->address,
                         'points'   => $u->points,
                         'created_at' => $u->created_at,
                     ];
                 });
    }

    // POST /api/v1/manager/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'role'     => ['required', Rule::in(['staff','customer'])], // manager not created via API
            'name'     => ['required','string','max:100'],
            'username' => ['required','alpha_num','min:4','max:30','unique:users,username'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
            'phone'    => ['nullable','string','max:20'],
            'address'  => ['nullable','string','max:255'],
            'points'   => ['nullable','integer','min:0'],
        ]);

        $user = User::create($data + ['points' => $data['points'] ?? 0]);

        return response()->json([
            'message' => 'User created.',
            'user'    => $user->only(['id','name','username','email','role','phone','address','points','created_at']),
        ], 201);
    }

    // GET /api/v1/manager/users/{user}
    public function show(User $user)
    {
        // If the user was soft-deleted, route model binding won’t find it by default.
        return response()->json([
            'user' => $user->only(['id','name','username','email','role','phone','address','points','created_at']),
        ]);
    }

    // PUT/PATCH /api/v1/manager/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role'     => ['required', Rule::in(['staff','customer'])],
            'name'     => ['required','string','max:100'],
            'username' => ['required','alpha_num','min:4','max:30', Rule::unique('users','username')->ignore($user->id)],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','confirmed','min:8'],
            'phone'    => ['nullable','string','max:20'],
            'address'  => ['nullable','string','max:255'],
            'points'   => ['nullable','integer','min:0'],
        ]);

        if (empty($data['password'])) unset($data['password']);

        $user->fill($data)->save();

        return response()->json([
            'message' => 'User updated.',
            'user'    => $user->only(['id','name','username','email','role','phone','address','points','created_at']),
        ]);
    }

    // DELETE /api/v1/manager/users/{user}
    public function destroy(User $user)
    {
        $user->delete(); // soft delete
        return response()->json(['message' => 'User deleted.']);
    }
}

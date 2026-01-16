<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:manager']);
    }

    /**
     * List users with search / role filter / sort / per-page.
     * Shows only staff + customer (no manager, no trashed).
     */
    public function index(Request $request)
    {
        $users = $this->filteredQuery($request)
            ->simplePaginate($this->perPage($request))
            ->withQueryString();

        $counts = [
            'staff'    => User::where('role', 'staff')->count(),
            'customer' => User::where('role', 'customer')->count(),
        ];

        return view('manager.users.index', [
            'users'   => $users,
            'search'  => trim((string) $request->input('search', '')),
            'role'    => $this->role($request),
            'sort'    => $this->sort($request),
            'dir'     => $this->dir($request),
            'perPage' => $this->perPage($request),
            'counts'  => $counts,
        ]);
    }

    public function create()
    {
        return view('manager.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role'                  => ['required', 'in:staff,customer'],
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_num', 'min:4', 'max:30', 'unique:users,username'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
            'points'                => ['nullable', 'integer', 'min:0'],
        ]);

        User::create($data); // password hashed via mutator/cast in User model

        return redirect()->route('manager.users.index')
            ->with('flash.success', 'User created.');
    }

    public function edit(User $user)
    {
        abort_if($user->role === 'manager', 403);
        return view('manager.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->role === 'manager', 403);

        $data = $request->validate([
            'role'                  => ['required', 'in:staff,customer'],
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_num', 'min:4', 'max:30', "unique:users,username,{$user->id}"],
            'email'                 => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password'              => ['nullable', 'confirmed', 'min:8'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
            'points'                => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('flash.success', 'User updated.');
    }

    /**
     * CSV export that respects the current filters.
     */
    public function export(Request $request)
    {
        $rows = $this->filteredQuery($request)->get([
            'id', 'role', 'name', 'username', 'email', 'phone', 'address', 'points', 'created_at'
        ]);

        $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','role','name','username','email','phone','address','points','created_at']);
            foreach ($rows as $u) {
                fputcsv($out, [
                    $u->id, $u->role, $u->name, $u->username, $u->email,
                    $u->phone, $u->address, $u->points, $u->created_at
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Delete user (soft delete).
     * Prevent deleting managers and the currently logged-in user.
     */
    public function destroy(User $user)
    {
        if ($user->role === 'manager') {
            abort(403, 'Cannot delete manager accounts.');
        }

        $currentId = Auth::id();

        if ($currentId && $currentId === $user->id) {
            return back()->with('flash.error', 'You cannot delete your own account while logged in.');
        }

        try {
            $user->delete(); // SoftDeletes on User
            return redirect()
                ->route('manager.users.index')
                ->with('flash.success', 'User deleted.');
        } catch (\Throwable $e) {
            return back()->with('flash.error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /* -------------- helpers -------------- */

    private function filteredQuery(Request $r): Builder
    {
        $q = User::query()->whereIn('role', ['staff', 'customer']);

        if ($role = $this->role($r)) {
            $q->where('role', $role);
        }

        if ($search = trim((string) $r->input('search', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $q->orderBy($this->sort($r), $this->dir($r))
                 ->orderBy('id', 'desc');
    }

    private function role(Request $r): ?string
    {
        $role = $r->input('role');
        return in_array($role, ['staff', 'customer'], true) ? $role : null;
    }

    private function sort(Request $r): string
    {
        $s = $r->input('sort', 'id');
        return in_array($s, ['id', 'name', 'created_at'], true) ? $s : 'id';
    }

    private function dir(Request $r): string
    {
        return $r->input('dir') === 'asc' ? 'asc' : 'desc';
    }

    private function perPage(Request $r): int
    {
        $pp = (int) $r->input('perPage', 12);
        return in_array($pp, [10, 12, 25, 50, 100], true) ? $pp : 12;
    }
}

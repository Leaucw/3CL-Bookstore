<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Book;

class BookPolicy
{
    public function viewAny(User $user): bool   { return in_array($user->role, ['manager','staff'], true); }
    public function view(User $user, Book $b): bool { return in_array($user->role, ['manager','staff'], true); }
    public function create(User $user): bool   { return in_array($user->role, ['manager','staff'], true); }
    public function update(User $user, Book $b): bool { return in_array($user->role, ['manager','staff'], true); }
    public function delete(User $user, Book $b): bool { return in_array($user->role, ['manager','staff'], true); }
}

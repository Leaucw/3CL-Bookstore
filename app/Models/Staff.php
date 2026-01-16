<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Staff extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        static::addGlobalScope('role_staff', function (Builder $q) {
            $q->where('role', 'staff');
        });

        static::creating(function ($model) {
            $model->role = 'staff';
        });
    }
}

<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Customer extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        static::addGlobalScope('role_customer', function (Builder $q) {
            $q->where('role', 'customer');
        });

        static::creating(function ($model) {
            $model->role = 'customer';
        });
    }

    public function addPoints(int $amount): void
    {
        $this->points = max(0, ($this->points ?? 0) + $amount);
        $this->save();
    }
}

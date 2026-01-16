<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /** Map role → subclass */
    public const ROLE_CLASS = [
        'manager'  => \App\Models\Manager::class,
        'staff'    => \App\Models\Staff::class,
        'customer' => \App\Models\Customer::class,
    ];

    protected $table = 'users';

    protected $fillable = [
        'name','username','email','password','role','phone','address','avatar_path','points'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts  = [
        'email_verified_at' => 'datetime',
        'points'            => 'integer',
        // keep your current mutator for hashing (below)
        // 'password' => 'hashed',  // <- do NOT enable unless remove the mutator
    ];

    // Keep existing mutator (so no behavior changes)
    public function setPasswordAttribute($value){
        if ($value) $this->attributes['password'] = bcrypt($value);
    }

    public function isManager(){ return $this->role === 'manager'; }
    public function isStaff(){   return $this->role === 'staff'; }
    public function isCustomer(){return $this->role === 'customer'; }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attr = (object) $attributes;

        if (isset($attr->role) && isset(static::ROLE_CLASS[$attr->role])) {
            $class = static::ROLE_CLASS[$attr->role];

            /** @var \App\Models\User $instance */
            $instance = (new $class)->newInstance([], true);
            $instance->setRawAttributes((array) $attr, true);
            $instance->setConnection($connection ?: $this->getConnectionName());

            return $instance;
        }

        return parent::newFromBuilder($attributes, $connection);
    }
}

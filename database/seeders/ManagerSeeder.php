<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ManagerSeeder extends Seeder
{
    public function run(): void
    {
        // Single, unchangeable manager account
        User::updateOrCreate(
            ['username' => 'manager'], // unique key
            [
                'name'     => 'Bookstore Manager',
                'email'    => 'manager@bookstore.local',
                'password' => 'Manager@123',  // hashed by User mutator
                'role'     => 'manager',
                'phone'    => '0123456789',
                'address'  => 'HQ',
                'points'   => 0,
            ]
        );
    }
}

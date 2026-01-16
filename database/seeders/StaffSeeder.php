<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $username = "staff{$i}";
            $email    = "{$username}@bookstore.local";

            User::updateOrCreate(
                ['username' => $username], // unique key
                [
                    'name'     => "Staff {$i}",
                    'email'    => $email,
                    'password' => 'Staff@123', // hashed via User mutator/cast
                    'role'     => 'staff',
                    'phone'    => '011' . str_pad((string)$i, 8, '0', STR_PAD_LEFT),
                    'address'  => "Branch #{$i}",
                    'points'   => 0,
                ]
            );
        }
    }
}

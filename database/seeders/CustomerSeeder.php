<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $email = "customer{$i}@bookstore.local";

            User::updateOrCreate(
                ['email' => $email], // unique key
                [
                    'username' => "customer{$i}",
                    'name'     => "Customer {$i}",
                    'password' => 'Customer@123', // hashed via User mutator/cast
                    'role'     => 'customer',
                    'phone'    => '019' . str_pad((string)$i, 7, '0', STR_PAD_LEFT),
                    'address'  => "No. {$i}, Customer Lane",
                    'points'   => 100,  //$i === 1 ? 100 : 0, first one gets starter points
                ]
            );
        }
    }
}

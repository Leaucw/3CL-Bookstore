<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ManagerSeeder::class,
            StaffSeeder::class,
            CustomerSeeder::class,
            CategorySeeder::class,
            EventSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}

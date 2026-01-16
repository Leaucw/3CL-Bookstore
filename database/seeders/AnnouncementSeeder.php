<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::firstOrCreate(
            ['title' => 'Flash Sale Coming Soon'],
            [
                'body' => 'Don’t miss our upcoming flash sale event. Discounts up to 50%!',
                'channels' => ['mail','sms'],
                'status' => 'scheduled',
            ]
        );

        Announcement::firstOrCreate(
            ['title' => 'Author Lecture Announced'],
            [
                'body' => 'Join us for a lecture by a famous author this weekend.',
                'channels' => ['mail'],
                'status' => 'scheduled',
            ]
        );
    }
}

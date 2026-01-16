<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::firstOrCreate(
            ['title' => 'Book Fair 2025'],
            [
                'slug' => Str::slug('Book Fair 2025') . '-' . uniqid(),
                'description' => 'A big event for book lovers with discounts and author meetups.',
                'type' => 'book_fair',
                'delivery_mode' => 'onsite',
                'starts_at' => now()->addDays(5),
                'ends_at' => now()->addDays(7),
                'visibility' => 'public',
                'status' => Event::SCHEDULED,
                'points_reward' => 20,
                'organizer_id' => 1, // staff user
            ]
        );

        Event::firstOrCreate(
            ['title' => 'Author Webinar'],
            [
                'slug' => Str::slug('Author Webinar') . '-' . uniqid(),
                'description' => 'Exclusive webinar session with bestselling authors.',
                'type' => 'webinar',
                'delivery_mode' => 'online',
                'starts_at' => now()->addDays(10),
                'ends_at' => now()->addDays(10)->addHours(2),
                'visibility' => 'public',
                'status' => Event::SCHEDULED,
                'points_reward' => 10,
                'organizer_id' => 1, // staff user
            ]
        );
    }
}

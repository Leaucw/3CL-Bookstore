<?php
//Leau Chee Way
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Fiction','Non-Fiction','Science','Biography','History',
            'Children','Young Adult','Mystery','Romance','Fantasy',
            'Horror','Thriller','Self-Help','Health & Wellness',
            'Travel','Cookbooks','Art & Photography','Business',
            'Technology','Education','Religion','Comics & Graphic Novels',
            'Poetry','Science Fiction','Politics',
        ];

         foreach ($categories as $name) {
            Category::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)] // 👈 generate slug
            );
        }
    }
}

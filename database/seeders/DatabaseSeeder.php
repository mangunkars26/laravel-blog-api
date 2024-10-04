<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    public function run()
    {
        // Generate 50 dummy posts
        Post::factory()->count(50)->create();
    }
}

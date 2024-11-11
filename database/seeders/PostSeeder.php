<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run()
    {
        // Post::factory(57)->create();
        // Menambahkan post dengan data tertentu
        Post::create([
            'user_id' => 1, // Bisa disesuaikan dengan user_id yang valid di database
            'category_id' => 1, // Pastikan category_id yang ada
            'title' => 'The Rise of Quantum Computing', // Contoh title
            'slug' => Str::slug('The Rise of Quantum Computing'), // Membuat slug dari title
            'body' => 'Quantum computing is the next frontier in technology that has the potential to revolutionize the world.',
            'status' => 'published', // Status bisa disesuaikan
            'scheduled_at' => now(), // Bisa disesuaikan jika ada waktu penjadwalan
        ]);

        // Menambahkan beberapa post lainnya
        Post::create([
            'user_id' => 2,
            'category_id' => 2,
            'title' => 'Understanding Artificial Intelligence',
            'slug' => Str::slug('Understanding Artificial Intelligence'),
            'body' => 'Artificial Intelligence (AI) is transforming industries across the world.',
            'status' => 'published',
            'scheduled_at' => now(),
        ]);

        // Menambahkan post dengan status scheduled
        Post::create([
            'user_id' => 3,
            'category_id' => 3,
            'title' => 'Exploring the Future of Cloud Computing',
            'slug' => Str::slug('Exploring the Future of Cloud Computing'),
            'body' => 'Cloud computing continues to shape the future of IT infrastructure.',
            'status' => 'scheduled',
            'scheduled_at' => now()->addDays(3), // Penjadwalan beberapa hari ke depan
        ]);

    }
}

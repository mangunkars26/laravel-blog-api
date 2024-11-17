<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    public function run()
    {
        // Inisialisasi Faker untuk menghasilkan data acak
        $faker = Faker::create();

        // Loop untuk membuat 20 post
        for ($i = 0; $i < 200; $i++) {
            // Pilih user_id dan category_id secara acak dari data yang ada
            $user_id = rand(1, 5); // Misal ada 5 user di database
            $category_id = rand(1, 5); // Misal ada 5 kategori di database

            // Buat post baru dengan data acak
            Post::create([
                'user_id' => $user_id,
                'category_id' => $category_id,
                'title' => $faker->sentence, // Judul acak
                'slug' => Str::slug($faker->sentence), // Slug dari judul
                'body' => $faker->paragraph(50), // Konten acak
                'status' => $faker->randomElement(['published', 'draft', 'scheduled']), // Status acak
                'scheduled_at' => $faker->randomElement([now(), now()->addDays(rand(1, 7))]), // Waktu penjadwalan acak
                'published_at' => $faker->randomElement([now(), now()->addDays(rand(1, 7))]), // Waktu publish acak
                'views_count' => $faker->numberBetween(0, 100), // Views count acak
                'meta_description' => $faker->sentence, // Meta description acak
                'meta_keywords' => $faker->words(5, true), // Meta keywords acak
                // 'tags' => json_encode($faker->words(3)), // Tags acak
            ]);
        }
    }
}

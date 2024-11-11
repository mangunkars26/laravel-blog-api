<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        // Daftar judul dengan tema teknologi pemrograman
        $titles = [
            'Mastering React with TypeScript',
            'Introduction to Laravel for Beginners',
            'Advanced Python Techniques for AI',
            'Building Scalable Microservices with Node.js',
            'Exploring Go for Backend Development',
            'Getting Started with Vue.js 3',
            'Understanding Blockchain Technology',
            'Serverless Architecture with AWS Lambda',
            'Diving into Rust for WebAssembly',
            'Best Practices for API Design in PHP',
            'The Future of Machine Learning: Trends to Watch',
            'Creating RESTful APIs with Laravel',
            'The Rise of Quantum Computing: What You Need to Know',
            'A Guide to Progressive Web Apps (PWAs)',
            'How to Optimize Your SQL Queries for Performance'
        ];

        $body = $this->faker->paragraphs(rand(12, 20), true);
        $title = $this->faker->randomElement($titles);

        // Mencari kategori yang sesuai dengan kata-kata dalam title atau body
        $category = $this->findMatchingCategory($title, $body);

        // Menentukan tag yang akan ditambahkan secara acak
        // $tags = Tag::inRandomOrder()->take(rand(2, 4))->pluck('id')->toArray();

        return [
            'title' => $title,
            'slug' => Str::slug($title . '-' . uniqid()),
            'body' => $body,
            'status' => $this->faker->randomElement(['draft', 'published', 'scheduled']),
            'featured_image' => $this->faker->imageUrl(640, 480, 'technology', true),
            'user_id' => User::inRandomOrder()->first()->id,
            'category_id' => $category ? $category->id : null, // Menetapkan category_id
        ];
    }

    /**
     * Mencari kategori yang cocok berdasarkan kata-kata dalam title dan body.
     *
     * @param string $title
     * @param string $body
     * @return \App\Models\Category|null
     */
    protected function findMatchingCategory(string $title, string $body)
    {
        // Menggabungkan kata-kata dalam title dan body
        $words = array_merge(explode(' ', strtolower($title)), explode(' ', strtolower($body)));

        // Menggunakan pencarian di database untuk menemukan kategori yang relevan
        foreach ($words as $word) {
            // Mencari kategori yang memiliki kata yang cocok dalam nama kategori
            $category = Category::where('name', 'like', '%' . $word . '%')->first();

            if ($category) {
                return $category; // Mengembalikan kategori pertama yang cocok
            }
        }

        return null; // Jika tidak ada kategori yang cocok
    }
}

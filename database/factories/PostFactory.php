<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

        // Menghasilkan konten panjang dengan random sentence
        $body = $this->faker->paragraphs(rand(3, 6), true);

        return [
            'title' => $this->faker->randomElement($titles),
            'slug' => Str::slug($this->faker->randomElement($titles)),
            'body' => $body,
            'status' => $this->faker->randomElement(['draft', 'published', 'scheduled']),
            'featured_image' => $this->faker->imageUrl(640, 480, 'tech', true),
            'user_id' => 1, // Ubah sesuai dengan user ID yang relevan di database
        ];
    }
}

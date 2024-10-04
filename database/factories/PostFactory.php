<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{

    protected $model = Post::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        return [
            'title' => $title,
            'slug' => Str::slug($title,),
            'body' => $this->faker->paragraphs(5, true),
            'status' => $this->faker->randomElement([
                'draft',
                'published',
                'scheduled'
            ]),
            'featured_image' => 'https://via.placeholder.com/640x480.png/00aa00?text=featured',
            'user_id' => \App\Models\User::factory(), 
            'published_at' => now(),
        ];
    }
}

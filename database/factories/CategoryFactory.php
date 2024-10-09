<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Daftar kategori teknologi web/programming
        $categories = [
            'Frontend Development',
            'Backend Development',
            'Full Stack Development',
            'Database',
            'Mobile Development',
            'DevOps',
            'Cloud Computing',
            'API Development',
            'Security',
            'AI & Machine Learning',
            'Data Science',
            'Testing',
            'UI/UX Design'
        ];

        $name = $this->faker->unique()->randomElement($categories);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Daftar tag teknologi spesifik di bidang web/programming
        $tags = [
            'React',
            'Vue.js',
            'Angular',
            'Laravel',
            'Node.js',
            'Django',
            'Flask',
            'MySQL',
            'PostgreSQL',
            'MongoDB',
            'GraphQL',
            'Docker',
            'Kubernetes',
            'AWS',
            'Azure',
            'Firebase',
            'Tailwind CSS',
            'Bootstrap',
            'SASS',
            'Redis',
            'Git',
            'CI/CD',
            'Jest',
            'Mocha',
            'Python',
            'JavaScript',
            'TypeScript',
            'PHP',
            'Ruby on Rails',
        ];

        $name = $this->faker->unique()->randomElement($tags);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}

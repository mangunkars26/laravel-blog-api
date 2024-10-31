<?php

namespace Database\Seeders;

use App\Models\Post;
use Database\Seeders\TagSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
    $this->call([
        UserSeeder::class,
        PostSeeder::class,

        // CategorySeeder::class,
        // TagSeeder::class,
    ]);
    }
}

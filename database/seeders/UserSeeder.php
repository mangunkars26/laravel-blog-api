<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name"=> "Nashih Amin",
            "email"=> "nashih.worok@outlook.com",
            "password"=> bcrypt("Ngipik_123"),
            "role"=> "admin",
        ]);

        // User::factory(5)->create();
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Models\User;

use App\Models\Category;
use App\Http\Controllers\Controller;



class StatsController extends Controller
{
    public function getStats()
    {
        // Mengambil jumlah total posts
        $totalPosts = Post::count();

        // Mengambil jumlah total categories
        $totalCategories = Category::count();

        // Mengambil total views dari semua posts
        $totalViews = Post::sum('views_count');

        $totalUsers = User::count();

        return response()->json([
            'total_posts' => $totalPosts,
            'total_categories' => $totalCategories,
            'total_views' => $totalViews,
            'total_users' => $totalUsers
        ]);
    }
}

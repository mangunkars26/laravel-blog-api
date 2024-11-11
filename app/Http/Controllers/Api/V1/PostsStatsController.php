<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostsStatsController extends Controller
{
    public function stats()
    {
        try {
            $totalPosts = Post::count();
            $publishedPosts = Post::where('status', 'publishes')->count();
            $draftPosts = Post::where('status', 'draft')->count();
            $scheduledPosts = Post::where('status', 'scheduled')->count();

            return response()->json([
                'success' => true,
                'message' => 'Posts stats sukses diambil',
                'data' => [
                    'total_posts' => $totalPosts,
                    'draft_posts' => $draftPosts,
                    'scheduled_posts' => $scheduledPosts 
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagagal ambil data',
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

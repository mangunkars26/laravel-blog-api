<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Post;
use App\Models\Category;
use App\Models\PostView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostFilterRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class PostController extends Controller
{
    public function getTotalViews()
    {
        $totalViews = Post::sum('views_count');

        return response()->json([
            'total_views' => $totalViews
        ]);
    }

    public function index(PostFilterRequest $request)
    {
        // Inisialisasi query untuk model Post
        $query = Post::with(['user', 'category'])
            ->where('status', 'published');

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan user_id
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan category_id
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter berdasarkan rentang tanggal created_at
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        // Filter berdasarkan pencarian pada title atau content
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('content', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Sorting data
        $validSortColumns = ['created_at', 'title', 'status'];
        $sortBy = in_array($request->get('sort_by'), $validSortColumns) ? $request->get('sort_by') : 'created_at';
        $order = $request->get('order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $order);

        $limit = min($request->get('limit', 20), 100);

        $publishedPosts = Post::where('status', 'published');

        $posts = $publishedPosts;
        $posts = $query->paginate($limit);

        // Response JSON untuk hasil postingan
        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully.',
            'data' => $posts
        ], 200);
    }

        

    public function getPostsByCategory(Request $request, $categorySlug)
    {
        // Ambil nilai limit dari request, default ke 50 jika tidak ada
        $limit = $request->input('limit', 50);
    
        // Cari kategori berdasarkan slug dengan posts yang memiliki relasi tambahan
        $category = Category::where('slug', $categorySlug)->first();
    
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
                'data' => null,
            ], 404);
        }
    
        $posts = Post::with(['tags', 'user:id,name'])
            ->where('status', 'published')
            ->where('category_id', $category->id)
            ->paginate($limit);
    
        $posts->getCollection()->transform(function ($post) {
            if ($post->tags->isEmpty()) {
                $post->tags = collect(['No Tags']);
            }
            return $post;
        });
    
        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => $posts,
        ]);
    }
    
    public function showBySLug($slug)
    {
        try {
                $post = Post::with(['category', 'user'])
                    ->where('slug', $slug)
                    ->where('status', 'published')
                    ->firstOrFail();

                $ipAddress = request()->ip();

                $lastView = PostView::where('post_id', $post->id)
                    ->where('ip_address', $ipAddress)
                    ->latest('created_at')
                    ->first();

                    $canIncrementView = true;

                    if ($lastView) {
                        $timeSinceLastView = now()->diffInMinutes($lastView->created_at);

                        if ($timeSinceLastView < 60 ){
                            $canIncrementView = false;
                        }
                    }

                    if ($canIncrementView) {
                        PostView::create([
                            'post_id' => $post->id,
                            'ip_address' => $ipAddress,
                        ]);

                        $post->increment('views');
                    }

                return response()->json([
                    'success' => true,
                    'message' => 'Postingan berhasil diambil',
                    'data' => [
                        'post' => $post,
                        'already_viewed' => !$canIncrementView,
                    ],
                ]);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Postingan dengan slug {$slug} tidak ditemukan",
                    'data' => null,
                ], 404);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil postingan',
                    'data' => null,
                ], 500);
            }
    }


public function getRelatedPosts($postId, $limit = 5)
{
    try {
        // Ambil post utama
        $post = Post::with('category')->find($postId);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan tidak ditemukan',
                'data' => null,
            ], 404);
        }

        // Pastikan postingan memiliki kategori
        $categoryId = $post->category->id ?? null;

        if (!$categoryId) {
            return response()->json([
                'success' => true,
                'message' => 'Postingan tidak memiliki kategori terkait',
                'data' => [],
            ], 200);
        }

        // Cache key untuk related posts
        $cacheKey = "related_posts_post_{$postId}";

        // Coba ambil data dari cache
        $relatedPosts = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($categoryId, $post, $limit) {
            return Post::with(['category:id,name', 'tags', 'user:id,name'])
                ->where('category_id', $categoryId)
                ->where('id', '!=', $post->id)
                ->orderBy('views_count', 'desc') // Urutkan berdasarkan popularitas
                ->limit($limit)
                ->get(['id', 'title', 'slug', 'excerpt', 'featured_image', 'views_count', 'published_at', 'user_id']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Related posts berhasil diambil',
            'data' => $relatedPosts,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengambil related posts',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
        



    // Get popular posts by views
        public function getPopularPosts()
        {
            try {
                $posts = Post::orderBy('views_count', 'desc')
                              ->limit(10)
                              ->get();
    
                return response()->json([
                    'success' => true,
                    'message' => 'Popular posts retrieved successfully',
                    'data' => $posts
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve popular posts',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
}

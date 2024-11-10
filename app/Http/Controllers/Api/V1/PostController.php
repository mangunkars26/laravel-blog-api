<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostFilterRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    public function allPosts()
    {
        try {
            $posts = Post::paginate(10);
            return response()->json(['data' => $posts]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index(PostFilterRequest $request)
    {
        // Inisialisasi query untuk model Post
        $query = Post::with(['user', 'category', 'tags']);

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
        $sortBy = $request->get('sort_by', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sortBy, $order);

        // Ambil jumlah limit hasil yang diminta, default ke 20
        $limit = $request->get('limit', 100);
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
        
            // Cari kategori berdasarkan slug
            $category = Category::where('slug', $categorySlug)->first();
        
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tak ditemukan',
                    'data' => null,
                ], 404);
            }
        
            // Ambil daftar posts yang memiliki kategori tertentu dan dengan relasi tambahan
            $posts = Post::with(['categories', 'tags', 'user:id,name'])
                ->where('status', 'published')
                ->whereHas('categories', function ($query) use ($category) {
                    $query->where('category_id', $category->id);
                })
                ->paginate($limit);
        
            // Menangani kasus jika tidak ada tags yang terkait menggunakan Collection each
            $posts->getCollection()->each(function ($post) {
                if ($post->tags->isEmpty()) {
                    $post->tags = collect(['No Tags']); // Mengganti dengan koleksi berisi 'No Tags' jika tidak ada tag
                }
            });
        
            // Kembalikan response dengan data posts
            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully',
                'data' => $posts,
            ]);
        }

    public function show($id)
        {
            try {

                $post = Post::with(['category', 'tags', 'user'])
                    ->where('id', $id)
                    ->where('status', 'published')
                    ->firstOrFail();


                return response()->json([
                    'success' => true,
                    'message' => 'Postingan berhasil diambil',
                    'data' => $post,
                ]);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Postingan dengan id {$id} tidak ditemukan",
                    'data' => null,
                ], 404);
            } catch (Exception $e) {
                Log::error("Error ketika ambil post: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil postingan',
                    'data' => null,
                ], 500);
            }
        }

    
        


//segera diperbaiki di akun chatGPT amin.degunner

// public function getRelatedPosts($postId, $limit = 5)
// {
//     $post = Post::with('category')->find($postId);
    
//     if (!$post) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Postingan tidak ditemukan',
//             'data' => null,
//         ], 404);
//     }

//     // Pastikan postingan memiliki kategori terkait
//     if (!$post->category) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Postingan tidak memiliki kategori terkait',
//             'data' => null,
//         ], 404);
//     }

//     // Mengambil postingan lain yang memiliki kategori yang sama
//     $relatedPosts = Post::with(['category', 'tags', 'user:id,name'])
//         ->where('category_id', $post->category->id)
//         ->where('id', '!=', $post->id)
//         ->limit($limit)
//         ->get();

//     return response()->json([
//         'success' => true,
//         'message' => 'Related posts berhasil diambil',
//         'data' => $relatedPosts,
//     ], 200);
// }

//         // Get popular posts by views
//         public function popularPosts()
//         {
//             try {
//                 $posts = Post::orderBy('views', 'desc')
//                               ->limit(10)
//                               ->get();
    
//                 return response()->json([
//                     'success' => true,
//                     'message' => 'Popular posts retrieved successfully',
//                     'data' => $posts
//                 ], 200);
//             } catch (\Exception $e) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Failed to retrieve popular posts',
//                     'error' => $e->getMessage()
//                 ], 500);
//             }
//         }


}

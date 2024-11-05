<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    public function index(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            // Validasi limit
            if (!is_numeric($limit) || $limit < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas jumlah postingan tidak valid',
                    'data' => null,
                ], 400);
            }

            // Mengambil hanya postingan yang berstatus 'published'
            $posts = Post::with(['categories', 'tags', 'author:id,name'])
                ->where('status', 'published')
                ->paginate($limit);    

            // Cek jika tidak ada postingan
            if ($posts->total() === 0) {
                return response()->json([
                    'success' => true, // Menjaga status sukses
                    'message' => 'Belum ada postingan yang tersedia', // Pesan untuk tidak ada postingan
                    'data' => [], // Mengembalikan array kosong
                ], 404); // Kembali dengan status 200
            }

            // Transformasi slug_url
            $posts->getCollection()->transform(function ($post) {
                $post->slug_url = url("/api/posts/" . $post->slug);
                return $post;
            });

            // Kembali dengan data postingan
            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil diambil',
                'data' => $posts,
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan database: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan tak terduga: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function getRelatedPosts($postId, $limit = 5)
    {
        $post = Post::find($postId);
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan tidak ditemukan',
                'data' => null,
            ], 404);
        }

        // Mengambil postingan yang memiliki kategori atau tag yang sama
        $relatedPosts = Post::with(['categories', 'tags', 'author:id,name'])
            ->whereHas('categories', function ($query) use ($post) {
                return $query->whereIn('id', $post->categories->pluck('id'));
            })
            ->where('id', '!=', $post->id)
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Related posts berhasil diambil',
            'data' => $relatedPosts,
        ], 200);
    }

    

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'body' => 'required',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $post = new Post($request->except('featured_image'));
            $post->user_id = Auth::id();
            $post->slug = $this->generateUniqueSlug($request->title);
            $post->save();

            if ($request->hasFile('featured_image')) {
                $post->featured_image = $this->handleFileUpload($request->file('featured_image'));
                $post->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil dibuat',
                'data' => $post,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat membuat post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat postingan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $slug)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'body' => 'required',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        try {
            $post = Post::where('slug', $slug)->firstOrFail();
            $post->update($request->except('featured_image'));

            if ($request->hasFile('featured_image')) {
                $post->featured_image = $this->handleFileUpload($request->file('featured_image'), $post->featured_image);
                $post->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil diperbarui',
                'data' => $post,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Post tidak ditemukan: ' . $slug);
            return response()->json([
                'success' => false,
                'message' => 'Postingan dengan slug tersebut tidak ditemukan.',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error('Kesalahan saat memperbarui post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui postingan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($slug)
    {
        try {
            $post = Post::with(['categories', 'tags'])->where('slug', $slug)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil diambil',
                'data' => $post,
            ]);
        } catch (Exception $e) {
            Log::error("Error ketika ambil post:" . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil postingan',
                'data' => null,
            ], 500);
        }
    }

    public function destroy($slug)
    {
        try {
            $post = Post::where('slug', $slug)->firstOrFail();

            if ($post->featured_image) {
                Storage::delete(str_replace('/storage/', '', $post->featured_image));
            }
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil dihapus',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan dengan slug tersebut tidak ditemukan.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Kesalahan saat menghapus post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus postingan.',
            ], 500);
        }
    }

    private function handleFileUpload($file, $currentImage = null)
    {
        if ($currentImage) {
            Storage::delete(str_replace('/storage/', '', $currentImage));
        }

        $path = $file->store('public/featured_images');
        return Storage::url($path);
    }

    private function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = Post::where('slug', 'like', "$slug%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}

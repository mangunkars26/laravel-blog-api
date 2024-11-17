<?php

namespace App\Http\Controllers\Api\V1;

use Log;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PostFilterRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdmPostController extends Controller
{

    

    // Get all posts with advanced filtering, sorting, and pagination
    public function index(PostFilterRequest $request)
    {
        $query = Post::query();

        // Apply filters
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('content', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Sorting and Pagination
        $sortBy = $request->get('sort_by', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 20);
        $posts = $query->orderBy($sortBy, $order)->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully.',
            'data' => $posts
        ], 200);
    }

    // Update status of a post (e.g., publish or draft)
    public function toPublish(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->status = $request->input('status', 'draft');
            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Post status updated successfully',
                'data' => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Batch delete posts by IDs
    public function batchDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $deletedCount = Post::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "$deletedCount posts deleted successfully",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Search posts by title or content
    public function searchPosts(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $posts = Post::where('title', 'like', "%$query%")
                          ->orWhere('content', 'like', "%$query%")
                          ->paginate($request->get('limit', 20));

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Handle file upload with optional existing image deletion
    private function handleFileUpload($file, $currentImage = null)
    {
        if ($currentImage) {
            Storage::delete(str_replace('/storage/', '', $currentImage));
        }

        $path = $file->store('public/featured_images');
        return Storage::url($path);
    }

    // Store a new post
    public function store(StorePostRequest $request)
    {
        DB::beginTransaction();
        try {
            // Debug request data
            // Log::info('Request data:', $request->all());

            $category = Category::where('name', $request->category->name)->first();

            if (!$category) {
                throw new \Exception('Category tidak ada');
            }

            $userId = $request->user()->id;

            $post = Post::create([
                'user_id' => $userId,
                'category_id' => $category->id,
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'body' => $request->body,
                'status' => $request->status ?? 'draft',
                'scheduled_at' => $request->status === 'schedule' ? $request->scheduled_at : null,
            ]);

        

            if ($request->hasFile('featured_image')) {
                $post->featured_image = $this->handleFileUpload($request->file('featured_image'));
                $post->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::error('Error creating post:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Show a specific post
    public function show($id)
    {
        try {
            $post = Post::with('category', 'user');
            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => $post
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing post
    public function update(UpdatePostRequest $request, $slug)
    {
        DB::beginTransaction();
        try {
            $post = Post::where('slug', $slug)->firstOrFail();
            $post->update([
                'user_id' => $request->user_id ?? $post->user_id,
                'category_id' => $request->category_id ?? $post->category_id,
                'title' => $request->title ?? $post->title,
                'slug' => $request->title ? Str::slug($request->title) : $post->slug,
                'body' => $request->body ?? $post->body,
                'status' => $request->status ?? $post->status,
                'scheduled_at' => $request->scheduled_at ?? $post->scheduled_at,
            ]);

            if ($request->hasFile('featured_image')) {
                if ($post->featured_image && Storage::exists($post->featured_image)) {
                    Storage::delete($post->featured_image);
                }

                $post->featured_image = $this->handleFileUpload($request->file('featured_image'));
                $post->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a post
    public function destroy($id)
    {
        try {
            $post = Post::where('id', $id)->firstOrFail();
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function moveToTrash($post)
    {
        $post->delete();
        $post->deleted_at = now();
        $post->save();
    }

    public function restore($id)
    {
        try {
            $post = Post::withTrashed()->findOrFail($id);
            $post->restore();

            return response()->json([
                'success' => true,
                'message' => 'Post sukses direstore',
                'data' => $post
            ],  200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}


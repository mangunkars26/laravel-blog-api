<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdatePostRequest;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdmPostController extends Controller
{
    // Get all posts

    public function index(Request $request)
{
    try {
        $query = Post::query();

        // Validate query parameters
        $validatedData = $request->validate([
            'status' => 'in:published,draft',
            'author_id' => 'integer|exists:users,id',
            'category_id' => 'integer|exists:categories,id',
            'date_from' => 'date|before_or_equal:date_to',
            'date_to' => 'date|after_or_equal:date_from',
            'sort_by' => 'in:created_at,updated_at,views',
            'order' => 'in:asc,desc',
            'limit' => 'integer|min:1|max:100',
            'search' => 'string|max:255'
        ], [
            'status.in' => 'Status must be either published or draft.',
            'author_id.exists' => 'The specified author does not exist.',
            'category_id.exists' => 'The specified category does not exist.',
            'date_from.date' => 'The start date must be a valid date.',
            'date_to.date' => 'The end date must be a valid date.',
            'sort_by.in' => 'Invalid sort column. Allowed columns: created_at, updated_at, views.',
            'order.in' => 'Order must be either asc or desc.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'search.max' => 'Search query too long, maximum 255 characters allowed.'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('content', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sortBy, $order);

        // Pagination
        $limit = $request->get('limit', 20);
        $posts = $query->paginate($limit);

        // Response
        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully.',
            'data' => $posts
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handling validation errors
        return response()->json([
            'success' => false,
            'message' => 'Invalid input parameters.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        // Handling unexpected errors
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve posts.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function toPublish (Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->status = $request->status('status', 'draft');
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
            $ids = $request->get('ids', []);
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
            $query = $request->get('query', '');
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

    // Get popular posts by views
    public function popularPosts()
    {
        try {
            $posts = Post::orderBy('views', 'desc')
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
            $post = Post::create([
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'body' => $request->body,
                'status' => $request->status ?? 'draft',
                'scheduled_at' => $request->scheduled_at,
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
            $post = Post::findOrFail($id);
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
        try {
            $post = Post::findOrFail($slug);
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
    public function destroy($slug)
    {
        try {
            $post = Post::where('slug', $slug)->firstOrFail();
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

    // Get statistics of posts
    public function stats()
    {
        try {
            $totalPosts = Post::count();
            $publishedPosts = Post::where('status', 'published')->count();
            $draftPosts = Post::where('status', 'draft')->count();
            $scheduledPosts = Post::where('status', 'scheduled')->count();

            return response()->json([
                'success' => true,
                'message' => 'Post statistics retrieved successfully',
                'data' => [
                    'total_posts' => $totalPosts,
                    'published_posts' => $publishedPosts,
                    'draft_posts' => $draftPosts,
                    'scheduled_posts' => $scheduledPosts,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

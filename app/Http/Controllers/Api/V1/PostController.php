<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::with(['categories', 'tags'])->paginate(10);
            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully',
                'data' => $posts,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching posts',
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        dd($request->all());
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'body' => 'required',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|string'
        ]);
    
        try {
            $post = new Post($request->all());
            $post->user_id = Auth::id();
            $post->slug = Str::slug($request->title);
            $post->save();

            if ($request->hasFile('featured_image')) {
                $path = $request->file('featured_image')->store('featured_images');
                $post['featured_image'] = Storage::url($path);
                $post->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage(),
                'data' => null,
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

            if($request->hasFile('featured_image')) {
                if($post->featured_image) {
                    Storage::delete(str_replace('/storage/', '', $post->featured_image));
                }
    
                $path = $request->file('featured_image')->store('featured_images');
                $post->featured_image = Storage::url($path);
            }

            $post->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating post: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    

    public function show($slug)
    {
        try {
            $post = Post::with(['categories', 'tags'])->where('slug', $slug)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => $post,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving post',
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
                'message' => 'Post deleted successfully',
                'data' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting post',
                'data' => null,
            ], 500);
        }
    }
}

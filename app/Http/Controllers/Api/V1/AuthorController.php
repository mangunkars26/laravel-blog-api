<?php



    namespace App\Http\Controllers\Api\V1;

    use App\Models\Post;
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;

    class AuthorController extends Controller
    {
    public function getAuthorPosts(Request $request)
    {
        $authorId = Auth::id();

        $status = $request->query('status');
        $keyword = $request->query('keyword');

        $query = Post::where('user_id', $authorId)
            ->with(['category', 'tags', 'comments'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                ->orWhere('content', 'like', "%$keyword%");
            });
        }

        $authorPosts = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data post author berhasil diambil',
            'data' => $authorPosts->items(),
            'pagination' => [
                'current_page' => $authorPosts->currentPage(),
                'last_page' => $authorPosts->lastPage(),
                'total' => $authorPosts->total(),
                'per_page' => $authorPosts->perPage(),
            ],
        ], 200);
    }

    public function showAuthorPost(Request $request, $authorName)
{
    // Ambil nilai limit dari request, default ke 10 jika tidak ada
    $limit = $request->input('limit', 10);

    // Cari user berdasarkan nama author dan role 'author'
    $author = User::where('name', $authorName)
                  ->where('role', 'author')
                  ->first();

    if (!$author) {
        return response()->json([
            'success' => false,
            'message' => 'Author tak ditemukan',
            'data' => null,
        ], 404);
    }

    // Ambil daftar posts berdasarkan author dengan relasi tambahan dan sorting
    $posts = Post::with(['categories', 'tags', 'user:id,name'])
        ->where('user_id', $author->id)
        ->where('status', 'published')
        ->when($request->input('sort') === 'popular', function ($query) {
            $query->orderBy('views_count', 'desc');
        }, function ($query) {
            $query->orderBy('created_at', 'desc');
        })
        ->paginate($limit);

    // Kembalikan response dengan data posts
    return response()->json([
        'success' => true,
        'message' => 'Author posts retrieved successfully',
        'data' => [
            'author' => $author->name,
            'posts' => $posts
        ],
    ]);
}


    public function showAuthorPostBySlug($authorName, $postSlug)
    {
        $author = User::where('name', $authorName)
                    ->where('role','author')
                    ->first();
    
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author tidak ditemukan',
                'data' => null,
            ], 404);
        }
    
        $post = Post::with(['categories', 'tags', 'user:id,name'])
            ->where('user_id', $author->id)
            ->where('slug', $postSlug)
            ->where('status', 'published')
            ->first();
    
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post tidak ditemukan',
                'data' => null,
            ], 404);
        }
    
        if ($post->tags->isEmpty()) {
            $post->tags = collect(['No Tag']);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'data' => $post,
        ]);
    }
    

}

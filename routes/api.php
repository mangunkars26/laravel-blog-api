<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{
    AuthController,
    PostController,
    AdmPostController,
    CategoryController,
    // CampaignController,
    // DonationController,
    StatsController,
    TagController,
    UserController,
    AuthorController,
    PostsStatsController
};

Route::prefix('v1')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh_token', [AuthController::class,'refreshToken']);
    });

    // Public routes
    Route::get('posts/popular', [PostController::class, 'getPopularPosts']);
        Route::get('/posts', [PostController::class, 'index']);
        Route::get('/posts/{slug}', [PostController::class, 'showBySlug']);
        Route::get('/posts/related/{postId}', [PostController::class, 'getRelatedPosts']);
        Route::get('/posts/{categorySlug}', [PostController::class, 'getPostsByCategory']);


    // Route::get('campaigns', [CampaignController::class, 'index']);
    // Route::get('campaigns/{id}', [CampaignController::class, 'show']);

    // Protected routes
    Route::middleware('auth:api')->group(function () {
        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('tags', TagController::class);
            Route::apiResource('users', UserController::class);

            Route::prefix('posts')->group(function () {
                Route::get('/', [AdmPostController::class, 'index']);
                Route::post('/', [AdmPostController::class, 'store']);
                Route::patch('/{id}', [AdmPostController::class, 'update']);
                Route::delete('/{id}', [AdmPostController::class, 'destroy']);
                Route::post('/batch-delete', [AdmPostController::class, 'batchDelete']);
                Route::get('/search', [AdmPostController::class, 'searchPosts']);
                Route::patch('/publish/{id}', [AdmPostController::class, 'toPublish']);
                Route::get('/stats', [PostsStatsController::class, 'stats']);
            });

            Route::get('stats', [StatsController::class, 'getStats']);
        });

        // Author-specific routes
        Route::middleware('role:author')->prefix('author/{authorName}')->group(function () {
            Route::get('posts', [AuthorController::class, 'getAuthorPosts']);
            Route::get('posts/{postSlug}', [AuthorController::class, 'showAuthorPostBySlug']);
        });

        // Campaign and donation routes
        Route::prefix('campaigns')->group(function () {
            Route::post('/', [CampaignController::class, 'store']);
        });

        Route::prefix('donations')->group(function () {
            Route::post('/', [DonationController::class, 'store']);
            Route::put('/status/{id}', [DonationController::class, 'updateStatus']);
        });
    });
});

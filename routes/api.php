<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\AdmPostController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DonationController;
use App\Http\Controllers\Api\V1\PostsStatsController;

// Grup rute dengan prefix 'v1'
Route::prefix('v1')->group( function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::get('/popular_posts', [PostController::class, 'getPopularPosts']);


    //PREFIX POSTS
        Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{slug}', [PostController::class, 'showBySlug']);
        Route::get('/related/{postId}', [PostController::class, 'getRelatedPosts']);
        Route::get('/{categoryslug}', [PostController::class, 'getPostsByCategory']);
    });

    Route::prefix('stats')->group(function () {
        Route::get('/posts/total-views', [PostController::class,'getTotalViews']);
    });

    //campaigns
    Route::get('campaigns', [CampaignController::class, 'index']);
    Route::get('campaigns/{id}', [CampaignController::class, 'show']);

    // Rute yang memerlukan autentikasi JWT
    Route::middleware(['auth:api'])->group(function () {

        // Rute yang memerlukan autentikasi admin dan author
        Route::middleware(['role:admin,author'])->prefix('admin/posts')->group(function () {

            //admincontroller
            Route::get('/', [AdmPostController::class, 'index']);
            Route::post('/create', [AdmPostController::class,'store']);
            Route::patch('/update/{id}', [AdmPostController::class,'update']);
            Route::delete('/delete/{id}', [AdmPostController::class,'destroy']);
            Route::get('/stats', [PostsStatsController::class,'stats']);
            Route::patch('/publish/{id}', [AdmPostController::class, 'toPublish']);
            Route::post('/batch-delete', [AdmPostController::class, 'batchDelete']);
            Route::get('/search', [AdmPostController::class, 'searchPosts']);

            //category untuk form create Post
            Route::get('/categories', [CategoryController::class, 'getCategory']);

            //stats
            // api.php
            Route::get('/stats', [StatsController::class, 'getStats']);

        });    

        Route::prefix('/author/{authorName}')->group(function () {
            Route::get('/posts', [AuthorController::class, 'getAuthorPosts']);
            Route::get('/{postSlug}', [AuthorController::class, 'showAuthorPostBySlug']);
            // Route::get('/profile', [AuthorController::class, 'getAuthorProfile']);
        })->middleware(['role:author']);
        

        Route::middleware(['role:admin'])->prefix('admin')->group(function (){
        Route::apiResource('/categories', CategoryController::class);
        Route::apiResource('/tags', TagController::class);
        });

        

        // Rute yang hanya dapat diakses oleh admin
        Route::middleware(['role:admin'])->group(function () {
            // Contoh: Rute untuk mengelola users (jika sudah ada UserController)
            Route::apiResource('/users', UserController::class);

            // Rute AuthController untuk admin
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/profile', [AuthController::class, 'profile']);
        });


        Route::middleware('auth:api')->post('campaigns', [CampaignController::class, 'store']);
        Route::middleware('auth:api')->post('donations', [DonationController::class, 'store']);
        Route::middleware('auth:api')->put('donations/status/{id}', [DonationController::class, 'updateStatus']);

    });
});


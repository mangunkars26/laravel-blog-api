<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\V1\AdmPostController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CategoryController;

// Grup rute dengan prefix 'v1'
Route::prefix('v1')->group( function () {
    
        // Grup rute untuk 'posts'
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']); // Menampilkan daftar postingan
        Route::get('/{slug}', [PostController::class, 'show']); // Menampilkan postingan berdasarkan slug
        // Route::get('/{id}', [PostController::class, 'show']); // Menampilkan postingan berdasarkan ID numerik
        Route::get('/popular', [PostController::class, 'popularPosts']); // Mendapatkan postingan popule
        Route::get('/related/{postId}', [PostController::class, 'getRelatedPosts']); // Mendapatkan postingan terkait berdasarkan post ID
        Route::get('/category/{categoryId}', [PostController::class, 'getPostsByCategory']); // Mendapatkan postingan berdasarkan kategori
    });

    // Rute yang tidak memerlukan autentikasi
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    });


    

    //campaigns
    Route::get('campaigns', [CampaignController::class, 'index']);
    Route::get('campaigns/{id}', [CampaignController::class, 'show']);

    // Rute yang memerlukan autentikasi JWT
    Route::middleware(['auth:api'])->group(function () {

 

        // Rute untuk mendapatkan data user saat ini (opsional, bisa digabung dengan profile)
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Rute yang memerlukan autentikasi admin dan author
        Route::middleware(['role:admin,author'])->prefix('admin/posts')->group(function () {

            //admincontroller
            Route::get('/', [AdmPostController::class, 'index']);
            Route::post('/create', [AdmPostController::class,'store']);
            Route::patch('/update/{id}', [AdmPostController::class,'update']);
            Route::delete('/{id}', [AdmPostController::class,'destroy']);
            Route::get('/stats', [AdmPostController::class,'stats']);
            Route::patch('/{id}/publish', [AdmPostController::class, 'toPublish']);
            Route::post('/batch-delete', [AdmPostController::class, 'batchDelete']);
            Route::get('/search', [AdmPostController::class, 'searchPosts']);
            Route::get('/popular', [AdmPostController::class, 'popularPosts']);

        });    

        Route::middleware(['role:admin, author'])->prefix('admin')->group(function (){
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
        Route::middleware('auth:api')->put('donations/{id}/status', [DonationController::class, 'updateStatus']);
    });
});

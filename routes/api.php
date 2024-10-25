<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CategoryController;

// Grup rute dengan prefix 'v1'
Route::group([], function () {

    // Rute yang tidak memerlukan autentikasi
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);

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
        Route::middleware(['role:admin,author'])->group(function () {
            // Routes untuk Posts
            Route::post('/posts', [PostController::class, 'store']);
            Route::put('/posts/{id}', [PostController::class, 'update']);
            Route::delete('/posts/{id}', [PostController::class, 'destroy']);

            // Routes untuk Categories dan Tags
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

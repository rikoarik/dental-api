<?php

use App\Http\Controllers\Api\Admin\ArticleController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\NewsController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\TipController;
use App\Http\Controllers\Api\Public\HomeController;
use App\Http\Controllers\Api\Public\InteractionController;
use Illuminate\Support\Facades\Route;

// PUBLIC API (Guest)
Route::prefix('public')->middleware('throttle:60,1')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);

    Route::post('/news/{id}/like', [InteractionController::class, 'likeNews'])
        ->middleware('throttle:20,1');
    Route::get('/articles/{slug}', [InteractionController::class, 'viewArticle']);
    Route::post('/articles/{slug}/like', [InteractionController::class, 'likeArticle'])
        ->middleware('throttle:20,1');
});

// ADMIN API
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        Route::apiResource('banners', BannerController::class);
        Route::apiResource('news', NewsController::class);
        Route::apiResource('articles', ArticleController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('tips', TipController::class);
    });
});

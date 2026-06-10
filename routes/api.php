<?php

use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\TipController as AdminTipController;
use App\Http\Controllers\Api\Public\ArticleController;
use App\Http\Controllers\Api\Public\AuthController as PublicAuthController;
use App\Http\Controllers\Api\Public\BannerController;
use App\Http\Controllers\Api\Public\BookmarkController;
use App\Http\Controllers\Api\Public\FaqController as PublicFaqController;
use App\Http\Controllers\Api\Public\NewsController;
use App\Http\Controllers\Api\Public\ProductController;
use App\Http\Controllers\Api\Public\TipController;
use Illuminate\Support\Facades\Route;

// PUBLIC API (Guest)
Route::prefix('public')->middleware('throttle:60,1')->group(function () {
    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/tips/today', [TipController::class, 'today']);

    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/{slug}', [NewsController::class, 'show']);
    Route::post('/news/{id}/like', [NewsController::class, 'like'])
        ->middleware('throttle:20,1');

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    Route::post('/articles/{slug}/like', [ArticleController::class, 'like'])
        ->middleware('throttle:20,1');

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/faqs', [PublicFaqController::class, 'index']);

    Route::prefix('auth')->group(function () {
        Route::post('/register', [PublicAuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('/login', [PublicAuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [PublicAuthController::class, 'logout']);
            Route::get('/profile', [PublicAuthController::class, 'profile']);
            Route::put('/profile', [PublicAuthController::class, 'updateProfile']);
        });
    });

    Route::middleware('auth:sanctum')->prefix('bookmarks')->group(function () {
        Route::get('/', [BookmarkController::class, 'index']);
        Route::post('/{slug}', [BookmarkController::class, 'store']);
        Route::delete('/{slug}', [BookmarkController::class, 'destroy']);
    });
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
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('banners', AdminBannerController::class);
        Route::apiResource('news', AdminNewsController::class);
        Route::apiResource('articles', AdminArticleController::class);
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('tips', AdminTipController::class);
        Route::apiResource('faqs', FaqController::class);
    });
});

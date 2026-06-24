<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\News;
use App\Models\Product;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        $recentNews = News::latest()
            ->take(5)
            ->get()
            ->map(fn (News $item) => [
                'type' => 'news',
                'id' => $item->id,
                'title' => $item->title,
                'image' => $item->cover_image_url,
                'is_published' => $item->is_published,
                'created_at' => $item->created_at,
            ]);

        $recentArticles = Article::latest()
            ->take(5)
            ->get()
            ->map(fn (Article $item) => [
                'type' => 'article',
                'id' => $item->id,
                'title' => $item->title,
                'image' => $item->cover_image_url,
                'is_published' => $item->is_published,
                'created_at' => $item->created_at,
            ]);

        $recentProducts = Product::latest()
            ->take(5)
            ->get()
            ->map(fn (Product $item) => [
                'type' => 'product',
                'id' => $item->id,
                'title' => $item->name,
                'image' => $item->product_image_url,
                'is_published' => $item->is_active,
                'created_at' => $item->created_at,
            ]);

        $recentContent = $recentNews
            ->concat($recentArticles)
            ->concat($recentProducts)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        return $this->success([
            'stats' => [
                'news_count' => News::count(),
                'articles_count' => Article::count(),
                'products_count' => Product::count(),
                'active_admins_count' => User::role('admin')->count(),
            ],
            'recent_content' => $recentContent,
        ], 'Dashboard berhasil dimuat.');
    }
}

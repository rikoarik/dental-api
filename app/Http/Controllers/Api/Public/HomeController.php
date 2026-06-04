<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Banner;
use App\Models\News;
use App\Models\Tip;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        $banners = Banner::where('is_active', true)->latest()->get();
        $latest_news = News::where('is_published', true)->latest()->take(3)->get();
        $popular_articles = Article::where('is_published', true)
            ->orderByDesc('view_count')
            ->orderByDesc('like_count')
            ->take(5)
            ->get();
        $daily_tip = Tip::where('is_active', true)->first();

        return $this->success([
            'banners' => $banners,
            'latest_news' => $latest_news,
            'popular_articles' => $popular_articles,
            'daily_tip' => $daily_tip,
        ], 'Data beranda berhasil dimuat.');
    }
}

<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\News;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class InteractionController extends Controller
{
    use ApiResponser;

    public function likeNews(int $id): JsonResponse
    {
        $news = News::where('is_published', true)->findOrFail($id);
        $news->increment('like_count');

        return $this->success(null, 'Berita berhasil disukai.');
    }

    public function likeArticle(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
        $article->increment('like_count');

        return $this->success(null, 'Artikel berhasil disukai.');
    }

    public function viewArticle(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
        $article->increment('view_count');

        return $this->success($article->fresh(), 'Detail artikel.');
    }
}

<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\HandlesPublicListing;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ApiResponser;
    use HandlesPublicListing;

    public function index(Request $request): JsonResponse
    {
        $query = Article::where('is_published', true);

        if ($request->query('sort') === 'popular') {
            $query->orderByDesc('view_count')->orderByDesc('like_count');
        } else {
            $query->latest();
        }

        $this->applyCategory($query, $request);
        $this->applySearch($query, $request);

        return $this->success(
            $this->applyLimitOrPaginate($query, $request),
            'Data artikel berhasil dimuat.'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->increment('view_count');

        return $this->success($article->fresh(), 'Detail artikel.');
    }

    public function like(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->increment('like_count');

        return $this->success(null, 'Artikel berhasil disukai.');
    }
}

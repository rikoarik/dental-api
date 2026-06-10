<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    use ApiResponser;

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->bookmarkedArticles()
            ->where('is_published', true)
            ->latest('bookmarks.created_at');

        if ($request->filled('page')) {
            $perPage = min(max((int) $request->query('per_page', 15), 1), 50);
            $articles = $query->paginate($perPage);
        } else {
            $articles = $query->get();
        }

        return $this->success($articles, 'Artikel tersimpan berhasil dimuat.');
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $request->user()->bookmarkedArticles()->syncWithoutDetaching([$article->id]);

        return $this->success(null, 'Artikel berhasil disimpan.', 201);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $request->user()->bookmarkedArticles()->detach($article->id);

        return $this->success(null, 'Artikel berhasil dihapus dari simpanan.');
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\HandlesAdminListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\StoreArticleRequest;
use App\Http\Requests\Admin\Article\UpdateArticleRequest;
use App\Models\Article;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ApiResponser;
    use HandlesAdminListing;

    public function index(Request $request): JsonResponse
    {
        $query = Article::latest();
        $this->applyAdminFilters($query, $request);

        return $this->success(
            $this->resolvePagination($query, $request),
            'Data artikel berhasil dimuat'
        );
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $article = Article::create($data);

        if ($request->hasFile('image')) {
            $article->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($article->refresh(), 'Artikel berhasil dibuat', 201);
    }

    public function show(Article $article): JsonResponse
    {
        return $this->success($article, 'Detail artikel');
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $article->update($data);

        if ($request->hasFile('image')) {
            $article->clearMediaCollection('cover_image');
            $article->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($article->refresh(), 'Artikel berhasil diperbarui');
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->clearMediaCollection('cover_image');
        $article->delete();

        return $this->success(null, 'Artikel berhasil dihapus');
    }
}

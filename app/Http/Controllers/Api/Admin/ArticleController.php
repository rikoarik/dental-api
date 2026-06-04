<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\StoreArticleRequest;
use App\Http\Requests\Admin\Article\UpdateArticleRequest;
use App\Models\Article;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        return $this->success(
            Article::latest()->paginate(15),
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

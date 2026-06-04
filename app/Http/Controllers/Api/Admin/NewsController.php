<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\News\StoreNewsRequest;
use App\Http\Requests\Admin\News\UpdateNewsRequest;
use App\Models\News;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        return $this->success(
            News::latest()->paginate(15),
            'Data berita berhasil dimuat'
        );
    }

    public function store(StoreNewsRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $news = News::create($data);

        if ($request->hasFile('image')) {
            $news->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($news->refresh(), 'Berita berhasil dibuat', 201);
    }

    public function show(News $news): JsonResponse
    {
        return $this->success($news, 'Detail berita');
    }

    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $news->update($data);

        if ($request->hasFile('image')) {
            $news->clearMediaCollection('cover_image');
            $news->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($news->refresh(), 'Berita berhasil diperbarui');
    }

    public function destroy(News $news): JsonResponse
    {
        $news->clearMediaCollection('cover_image');
        $news->delete();

        return $this->success(null, 'Berita berhasil dihapus');
    }
}

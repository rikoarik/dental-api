<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\HandlesPublicListing;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    use ApiResponser;
    use HandlesPublicListing;

    public function index(Request $request): JsonResponse
    {
        $query = News::where('is_published', true)->latest();
        $this->applyCategory($query, $request);
        $this->applySearch($query, $request);

        return $this->success(
            $this->applyLimitOrPaginate($query, $request),
            'Data berita berhasil dimuat.'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $news = News::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $news->increment('view_count');

        return $this->success($news->fresh(), 'Detail berita.');
    }

    public function like(int $id): JsonResponse
    {
        $news = News::where('is_published', true)->findOrFail($id);
        $news->increment('like_count');

        return $this->success(null, 'Berita berhasil disukai.');
    }
}

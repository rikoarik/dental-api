<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\HandlesPublicListing;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Product;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponser;
    use HandlesPublicListing;

    public function index(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true)->latest();
        $this->applyCategory($query, $request);
        $this->applySearch($query, $request, 'name');

        return $this->success(
            $this->applyLimitOrPaginate($query, $request),
            'Data produk berhasil dimuat.'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedArticles = Article::where('is_published', true)
            ->orderByDesc('view_count')
            ->orderByDesc('like_count')
            ->take(3)
            ->get();

        return $this->success([
            'product' => $product,
            'related_articles' => $relatedArticles,
        ], 'Detail produk.');
    }
}

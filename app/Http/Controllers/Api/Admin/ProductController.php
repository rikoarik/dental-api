<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\HandlesAdminListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponser;
    use HandlesAdminListing;

    public function index(Request $request): JsonResponse
    {
        $query = Product::latest();
        $this->applyActiveFilter($query, $request);

        return $this->success(
            $this->resolvePagination($query, $request),
            'Data katalog obat berhasil dimuat'
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product_image');
        }

        return $this->success($product->refresh(), 'Katalog obat berhasil dibuat', 201);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->success($product, 'Detail katalog obat');
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $product->update($data);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('product_image');
            $product->addMediaFromRequest('image')->toMediaCollection('product_image');
        }

        return $this->success($product->refresh(), 'Katalog obat berhasil diperbarui');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->clearMediaCollection('product_image');
        $product->delete();

        return $this->success(null, 'Katalog obat berhasil dihapus');
    }
}

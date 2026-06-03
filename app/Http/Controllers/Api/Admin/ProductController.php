<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(Product::latest()->get(), 'Data katalog obat berhasil dimuat');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'dosage' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'usage_instructions', 'dosage', 'is_active']);
        $data['slug'] = Str::slug($request->name) . '-' . time();
        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product_image');
        }

        return $this->success($product, 'Katalog obat berhasil dibuat', 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return $this->success($product, 'Detail katalog obat');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'dosage' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::findOrFail($id);
        
        $data = $request->only(['name', 'description', 'usage_instructions', 'dosage', 'is_active']);
        if ($request->has('name') && $request->name !== $product->name) {
            $data['slug'] = Str::slug($request->name) . '-' . time();
        }
        
        $product->update($data);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('product_image');
            $product->addMediaFromRequest('image')->toMediaCollection('product_image');
        }

        return $this->success($product, 'Katalog obat berhasil diperbarui');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->clearMediaCollection('product_image');
        $product->delete();

        return $this->success(null, 'Katalog obat berhasil dihapus');
    }
}

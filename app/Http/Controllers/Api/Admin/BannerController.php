<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(Banner::latest()->get(), 'Data banner berhasil dimuat');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'link_url' => 'nullable|url',
            'is_active' => 'boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $banner = Banner::create($request->only(['title', 'link_url', 'is_active']));

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('banner_image');
        }

        return $this->success($banner, 'Banner berhasil dibuat', 201);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return $this->success($banner, 'Detail banner');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string|max:255',
            'link_url' => 'nullable|url',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $banner = Banner::findOrFail($id);
        $banner->update($request->only(['title', 'link_url', 'is_active']));

        if ($request->hasFile('image')) {
            $banner->clearMediaCollection('banner_image');
            $banner->addMediaFromRequest('image')->toMediaCollection('banner_image');
        }

        return $this->success($banner, 'Banner berhasil diperbarui');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->clearMediaCollection('banner_image');
        $banner->delete();

        return $this->success(null, 'Banner berhasil dihapus');
    }
}

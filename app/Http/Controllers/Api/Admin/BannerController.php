<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Models\Banner;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        return $this->success(
            Banner::latest()->paginate(15),
            'Data banner berhasil dimuat'
        );
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $banner = Banner::create($data);

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('banner_image');
        }

        return $this->success($banner->refresh(), 'Banner berhasil dibuat', 201);
    }

    public function show(Banner $banner): JsonResponse
    {
        return $this->success($banner, 'Detail banner');
    }

    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $banner->update($data);

        if ($request->hasFile('image')) {
            $banner->clearMediaCollection('banner_image');
            $banner->addMediaFromRequest('image')->toMediaCollection('banner_image');
        }

        return $this->success($banner->refresh(), 'Banner berhasil diperbarui');
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $banner->clearMediaCollection('banner_image');
        $banner->delete();

        return $this->success(null, 'Banner berhasil dihapus');
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tip\StoreTipRequest;
use App\Http\Requests\Admin\Tip\UpdateTipRequest;
use App\Models\Tip;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipController extends Controller
{
    use ApiResponser;
    use Concerns\HandlesAdminListing;

    public function index(Request $request): JsonResponse
    {
        return $this->success(
            $this->resolvePagination(Tip::latest(), $request),
            'Data tip harian berhasil dimuat'
        );
    }

    public function store(StoreTipRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $tip = Tip::create($data);

        if ($request->hasFile('image')) {
            $tip->addMediaFromRequest('image')->toMediaCollection('tip_image');
        }

        return $this->success($tip->refresh(), 'Tip harian berhasil dibuat', 201);
    }

    public function show(Tip $tip): JsonResponse
    {
        return $this->success($tip, 'Detail tip harian');
    }

    public function update(UpdateTipRequest $request, Tip $tip): JsonResponse
    {
        $data = collect($request->validated())->except('image')->all();
        $tip->update($data);

        if ($request->hasFile('image')) {
            $tip->clearMediaCollection('tip_image');
            $tip->addMediaFromRequest('image')->toMediaCollection('tip_image');
        }

        return $this->success($tip->refresh(), 'Tip harian berhasil diperbarui');
    }

    public function destroy(Tip $tip): JsonResponse
    {
        $tip->clearMediaCollection('tip_image');
        $tip->delete();

        return $this->success(null, 'Tip harian berhasil dihapus');
    }
}

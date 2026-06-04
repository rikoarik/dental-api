<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tip\StoreTipRequest;
use App\Http\Requests\Admin\Tip\UpdateTipRequest;
use App\Models\Tip;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class TipController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        return $this->success(
            Tip::latest()->paginate(15),
            'Data tip harian berhasil dimuat'
        );
    }

    public function store(StoreTipRequest $request): JsonResponse
    {
        $tip = Tip::create($request->validated());

        return $this->success($tip, 'Tip harian berhasil dibuat', 201);
    }

    public function show(Tip $tip): JsonResponse
    {
        return $this->success($tip, 'Detail tip harian');
    }

    public function update(UpdateTipRequest $request, Tip $tip): JsonResponse
    {
        $tip->update($request->validated());

        return $this->success($tip, 'Tip harian berhasil diperbarui');
    }

    public function destroy(Tip $tip): JsonResponse
    {
        $tip->delete();

        return $this->success(null, 'Tip harian berhasil dihapus');
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
use App\Models\Faq;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponser;
    use Concerns\HandlesAdminListing;

    public function index(Request $request): JsonResponse
    {
        return $this->success(
            $this->resolvePagination(Faq::orderBy('sort_order'), $request),
            'Data FAQ berhasil dimuat'
        );
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = Faq::create($request->validated());

        return $this->success($faq, 'FAQ berhasil dibuat', 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return $this->success($faq, 'Detail FAQ');
    }

    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $faq->update($request->validated());

        return $this->success($faq->refresh(), 'FAQ berhasil diperbarui');
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return $this->success(null, 'FAQ berhasil dihapus');
    }
}

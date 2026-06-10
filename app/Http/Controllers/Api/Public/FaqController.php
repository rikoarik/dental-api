<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->success($faqs, 'Data FAQ berhasil dimuat.');
    }
}

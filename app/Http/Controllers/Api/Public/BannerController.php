<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        $banners = Banner::where('is_active', true)
            ->latest()
            ->get();

        return $this->success($banners, 'Data banner berhasil dimuat.');
    }
}

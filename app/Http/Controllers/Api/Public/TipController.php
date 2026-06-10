<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class TipController extends Controller
{
    use ApiResponser;

    public function today(): JsonResponse
    {
        $tip = Tip::where('is_active', true)->first();

        if (! $tip) {
            return $this->error('Tips hari ini belum tersedia.', 404);
        }

        return $this->success($tip, 'Tips hari ini berhasil dimuat.');
    }
}

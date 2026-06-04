<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    protected function success(mixed $data, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(string $message, int $code): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }

    protected function validationError(array $errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => [
                'errors' => $errors,
            ],
        ], 422);
    }
}

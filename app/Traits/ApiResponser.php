<?php

namespace App\Traits;

trait ApiResponser
{
    /**
     * Return a success JSON response.
     */
    protected function success($data, string $message = null, int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $message, int $code)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null
        ], $code);
    }
}
<?php

use App\Http\Controllers\SwaggerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', [SwaggerController::class, 'index']);
Route::get('/api/docs/openapi.yaml', [SwaggerController::class, 'spec']);

Route::get('/create-storage-link-dental-2026', function () {
    try {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (file_exists($link)) {
            return response()->json([
                'message' => 'Storage link already exists',
                'target' => $target,
                'link' => $link
            ]);
        }

        if (!file_exists($target)) {
            mkdir($target, 0755, true);
        }

        symlink($target, $link);

        return response()->json([
            'message' => 'Storage link created successfully',
            'target' => $target,
            'link' => $link
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create storage link',
            'error' => $e->getMessage()
        ], 500);
    }
});

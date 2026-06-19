<?php

use App\Http\Controllers\SwaggerController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        if (! file_exists($target)) {
            mkdir($target, 0755, true);
        }

        $sourceFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
        );

        $sourceFileCount = 0;
        foreach ($sourceFiles as $sourceFile) {
            if ($sourceFile->isFile() && $sourceFile->getFilename() !== '.gitignore') {
                $sourceFileCount++;
            }
        }

        if (is_link($link)) {
            return response()->json([
                'message' => 'Storage link already exists',
                'target' => $target,
                'link' => $link,
                'source_file_count' => $sourceFileCount,
            ]);
        }

        if (file_exists($link) && ! is_dir($link)) {
            return response()->json([
                'message' => 'public/storage exists but is not a directory or symlink',
                'target' => $target,
                'link' => $link,
            ], 500);
        }

        if (! file_exists($link) && function_exists('symlink') && @symlink($target, $link)) {
            return response()->json([
                'message' => 'Storage link created successfully',
                'target' => $target,
                'link' => $link,
                'source_file_count' => $sourceFileCount,
            ]);
        }

        if (! file_exists($link)) {
            mkdir($link, 0755, true);
        }

        $copiedFiles = 0;
        $copyDirectory = function (string $source, string $destination) use (&$copyDirectory, &$copiedFiles): void {
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            foreach (new DirectoryIterator($source) as $item) {
                if ($item->isDot() || $item->getFilename() === '.gitignore') {
                    continue;
                }

                $sourcePath = $item->getPathname();
                $destinationPath = $destination.DIRECTORY_SEPARATOR.$item->getFilename();

                if ($item->isDir()) {
                    $copyDirectory($sourcePath, $destinationPath);
                    continue;
                }

                if (copy($sourcePath, $destinationPath)) {
                    $copiedFiles++;
                }
            }
        };

        $copyDirectory($target, $link);

        return response()->json([
            'message' => 'Symlink unavailable, copied storage files to public/storage',
            'target' => $target,
            'link' => $link,
            'source_file_count' => $sourceFileCount,
            'copied_file_count' => $copiedFiles,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Failed to prepare public storage',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/repair-production-dental-2026', function () {
    try {
        $deleted = [];
        $patterns = [
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/events.php'),
            base_path('bootstrap/cache/routes-*.php'),
            storage_path('framework/views/*.php'),
        ];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file) && @unlink($file)) {
                    $deleted[] = $file;
                }
            }
        }

        $passwordResetTokens = 'exists';
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });

            $passwordResetTokens = 'created';
        }

        return response()->json([
            'message' => 'Production repair completed. Retry the failed request now.',
            'deleted_cache_files' => $deleted,
            'password_reset_tokens' => $passwordResetTokens,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Production repair failed',
            'error' => $e->getMessage(),
        ], 500);
    }
});

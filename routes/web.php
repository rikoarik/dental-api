<?php

use App\Http\Controllers\SwaggerController;
use App\Models\User;
use App\Notifications\ResetPasswordOtp;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', [SwaggerController::class, 'index']);
Route::get('/api/docs/openapi.yaml', [SwaggerController::class, 'spec']);

Route::get('/storage-health-dental-2026', function () {
    $storageAppPublic = storage_path('app/public');
    $publicStorage = public_path('storage');
    $mediaDisk = config('media-library.disk_name');
    $filesystemDefault = config('filesystems.default');

    $diskReport = function (string $disk): array {
        $root = config("filesystems.disks.{$disk}.root");

        return [
            'root' => $root,
            'url' => config("filesystems.disks.{$disk}.url"),
            'exists' => is_string($root) && file_exists($root),
            'is_directory' => is_string($root) && is_dir($root),
            'is_writable' => is_string($root) && is_dir($root) && is_writable($root),
        ];
    };

    $recommendation = $mediaDisk === 'public_uploads'
        ? 'MEDIA_DISK=public_uploads is active. New uploads should write directly to public/storage, which is recommended for shared hosting.'
        : 'For shared hosting, set MEDIA_DISK=public_uploads and FILESYSTEM_DISK=public_uploads, then clear config cache and rerun /create-storage-link-dental-2026.';

    return response()->json([
        'message' => 'Storage health report generated.',
        'app_url' => config('app.url'),
        'media_disk' => $mediaDisk,
        'filesystem_default' => $filesystemDefault,
        'disks' => [
            'public' => $diskReport('public'),
            'public_uploads' => $diskReport('public_uploads'),
        ],
        'paths' => [
            'storage_app_public' => [
                'path' => $storageAppPublic,
                'exists' => file_exists($storageAppPublic),
                'is_directory' => is_dir($storageAppPublic),
                'is_writable' => is_dir($storageAppPublic) && is_writable($storageAppPublic),
            ],
            'public_storage' => [
                'path' => $publicStorage,
                'exists' => file_exists($publicStorage),
                'is_directory' => is_dir($publicStorage),
                'is_symlink' => is_link($publicStorage),
                'symlink_target' => is_link($publicStorage) ? readlink($publicStorage) : null,
                'is_writable' => is_dir($publicStorage) && is_writable($publicStorage),
            ],
        ],
        'example_public_url' => rtrim(config('app.url'), '/').'/storage/39/Privy-User-Consent.png',
        'recommendation' => $recommendation,
    ]);
});

Route::get('/create-storage-link-dental-2026', function () {
    try {
        $target = storage_path('app/public');
        $link = public_path('storage');
        $createdDirectories = [];

        if (! file_exists($target)) {
            mkdir($target, 0755, true);
            $createdDirectories[] = $target;
        }

        $countFiles = function (string $directory): int {
            if (! is_dir($directory)) {
                return 0;
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
            );

            $count = 0;
            foreach ($files as $file) {
                if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                    $count++;
                }
            }

            return $count;
        };

        $sourceFileCount = $countFiles($target);

        if (is_link($link)) {
            return response()->json([
                'message' => 'Storage symlink already exists. Public storage is ready.',
                'mode' => 'symlink-existing',
                'target' => $target,
                'link' => $link,
                'symlink_target' => readlink($link),
                'source_file_count' => $sourceFileCount,
                'media_disk' => config('media-library.disk_name'),
                'recommendation' => 'For shared hosting, MEDIA_DISK=public_uploads avoids depending on this symlink for future uploads.',
            ]);
        }

        if (file_exists($link) && ! is_dir($link)) {
            return response()->json([
                'message' => 'public/storage exists but is not a directory or symlink. Rename or remove it before repairing storage.',
                'target' => $target,
                'link' => $link,
                'media_disk' => config('media-library.disk_name'),
            ], 500);
        }

        if (! file_exists($link) && function_exists('symlink') && @symlink($target, $link)) {
            return response()->json([
                'message' => 'Storage symlink created successfully. Public storage is ready.',
                'mode' => 'symlink-created',
                'target' => $target,
                'link' => $link,
                'symlink_target' => readlink($link),
                'source_file_count' => $sourceFileCount,
                'media_disk' => config('media-library.disk_name'),
                'recommendation' => 'For shared hosting, MEDIA_DISK=public_uploads avoids depending on this symlink for future uploads.',
            ]);
        }

        if (! file_exists($link)) {
            mkdir($link, 0755, true);
            $createdDirectories[] = $link;
        }

        $copiedFiles = 0;
        $skippedFiles = 0;
        $failedFiles = [];
        $copyDirectory = function (string $source, string $destination) use (&$copyDirectory, &$copiedFiles, &$skippedFiles, &$failedFiles): void {
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

                if (file_exists($destinationPath) && filesize($destinationPath) === filesize($sourcePath)) {
                    $skippedFiles++;
                    continue;
                }

                if (@copy($sourcePath, $destinationPath)) {
                    $copiedFiles++;
                    continue;
                }

                $failedFiles[] = [
                    'source' => $sourcePath,
                    'destination' => $destinationPath,
                ];
            }
        };

        $copyDirectory($target, $link);

        return response()->json([
            'message' => empty($failedFiles)
                ? 'Symlink unavailable; copied existing storage files to public/storage.'
                : 'Symlink unavailable; copied some files but failed to copy others.',
            'mode' => 'copy-fallback',
            'target' => $target,
            'link' => $link,
            'created_directories' => $createdDirectories,
            'source_file_count' => $sourceFileCount,
            'public_file_count' => $countFiles($link),
            'copied_file_count' => $copiedFiles,
            'skipped_file_count' => $skippedFiles,
            'failed_file_count' => count($failedFiles),
            'failed_files' => array_slice($failedFiles, 0, 20),
            'media_disk' => config('media-library.disk_name'),
            'filesystem_default' => config('filesystems.default'),
            'recommendation' => 'Set MEDIA_DISK=public_uploads and FILESYSTEM_DISK=public_uploads in production so future uploads write directly to public/storage on shared hosting.',
        ], empty($failedFiles) ? 200 : 500);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Failed to prepare public storage',
            'error' => $e->getMessage(),
            'media_disk' => config('media-library.disk_name'),
            'filesystem_default' => config('filesystems.default'),
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

Route::get('/debug-forgot-password-dental-2026', function () {
    $email = (string) request('email', 'rikoarik04@gmail.com');
    $otp = (string) random_int(100000, 999999);
    $report = [
        'php_version' => PHP_VERSION,
        'mail' => [
            'default' => config('mail.default'),
            'scheme' => config('mail.mailers.smtp.scheme'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'password_set' => filled(config('mail.mailers.smtp.password')),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ],
        'password_reset_tokens' => [
            'exists' => Schema::hasTable('password_reset_tokens'),
            'columns' => Schema::hasTable('password_reset_tokens')
                ? Schema::getColumnListing('password_reset_tokens')
                : [],
        ],
    ];

    try {
        $user = User::where('email', $email)->first();
        $report['user_found'] = (bool) $user;

        if (! $user) {
            return response()->json($report, 404);
        }

        $report['step'] = 'update_password_reset_tokens';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        $report['step'] = 'render_email_view';
        view('emails.reset-password-otp', [
            'appName' => config('app.name', 'Dental Health'),
            'email' => $email,
            'otp' => $otp,
            'expiresInMinutes' => 60,
        ])->render();

        $report['step'] = 'send_notification';
        $user->notify(new ResetPasswordOtp($otp));

        $report['step'] = 'done';
        $report['message'] = 'Debug forgot-password completed successfully.';

        return response()->json($report);
    } catch (\Throwable $e) {
        $report['error'] = [
            'class' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        return response()->json($report, 500);
    }
});

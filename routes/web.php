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

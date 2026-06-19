<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ForgotPasswordRequest;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\Admin\Auth\RegisterRequest;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordOtp;
use App\Traits\ApiResponser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponser;

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return $this->error('Email atau password salah', 401);
        }

        if (! $user->hasRole('admin')) {
            return $this->error('Akun ini bukan akun admin.', 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login berhasil');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('admin');

        return $this->success($user, 'Admin baru berhasil didaftarkan', 201);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success($request->user(), 'Profil berhasil dimuat');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            return $this->error("We can't find a user with that email address.", 400);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        $user->notify(new ResetPasswordOtp($otp));

        return $this->success(null, 'Kode OTP reset password sudah dikirim ke email.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $record = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (
            ! $record ||
            ! $record->created_at ||
            Carbon::parse($record->created_at)->lt(now()->subMinutes(60)) ||
            ! Hash::check($data['otp'], $record->token)
        ) {
            return $this->error('Kode OTP reset password tidak valid atau sudah kedaluwarsa.', 400);
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return $this->error("We can't find a user with that email address.", 400);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->setRememberToken(Str::random(60));

        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        event(new PasswordReset($user));

        return $this->success(null, 'Password berhasil direset.');
    }
}

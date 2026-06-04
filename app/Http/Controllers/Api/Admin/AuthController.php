<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ForgotPasswordRequest;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\Admin\Auth\RegisterRequest;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->success(null, __($status))
            : $this->error(__($status), 400);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->success(null, __($status))
            : $this->error(__($status), 400);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponser;

    /**
     * Admin Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Email atau password salah', 401);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ], 'Login berhasil');
    }

    /**
     * Admin Register (Protected Route - Only existing admins can create new admins)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Optional: Jika pakai Spatie Permissions, Anda bisa assign role di sini
        // $user->assignRole('admin');

        return $this->success($user, 'Admin baru berhasil didaftarkan', 201);
    }

    /**
     * Get Auth Profile
     */
    public function profile(Request $request)
    {
        return $this->success($request->user(), 'Profil berhasil dimuat');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout berhasil');
    }

    /**
     * Lupa Password (Send Reset Link)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? $this->success(null, __($status))
                    : $this->error(__($status), 400);
    }

    /**
     * Reset Password (mengubah password menggunakan token)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
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
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerificationCodeNotification;

class AuthController extends Controller
{
    /**
     * Registrasi pengguna baru.
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|string|min:6|confirmed'
        ]);
    
        // Buat kode verifikasi misalnya 6 digit angka
        $verificationCode = random_int(100000, 999999);
    
        $user = User::create([
            'name'              => $validatedData['name'],
            'email'             => $validatedData['email'],
            'password'          => bcrypt($validatedData['password']),
            'verification_code' => $verificationCode,
        ]);
    
        // Kirim notifikasi verifikasi kode
        $user->notify(new VerificationCodeNotification($verificationCode));
    
        return response()->json([
            'email' => $validatedData['email'],
            'message' => 'User berhasil didaftarkan. Silakan cek email untuk kode verifikasi.'
        ], 201);
    }

    /**
     * Login pengguna dan buat token menggunakan Laravel Sanctum.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak sesuai.']
            ]);
        }

        $user = User::where('email',$credentials['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Logout pengguna dengan mencabut semua token yang dimiliki.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Verifikasi email pengguna.
     * Rute biasanya diakses melalui link verifikasi yang dikirim ke email.
     */
    public function verifyEmail(Request $request)
    {
        // Asumsikan rute mengirimkan parameter {id} dan query string hash
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi'], 400);
        }

        if ($user->markEmailAsVerified()) {
            // Event Verified dapat dikirim jika diperlukan
        }

        return response()->json(['message' => 'Email berhasil diverifikasi']);
    }

    /**
     * Mengirim ulang email verifikasi kepada pengguna.
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email verifikasi telah dikirim ulang']);
    }

    /**
     * Mengirim email link reset password.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }

        return response()->json(['message' => __($status)], 400);
    }

    /**
     * Melakukan reset password menggunakan token yang sudah dikirimkan.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        return response()->json(['message' => __($status)], 400);
    }
}

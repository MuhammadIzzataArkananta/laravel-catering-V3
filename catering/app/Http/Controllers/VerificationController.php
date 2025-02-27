<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;


class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required'
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->verification_code)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 400);
        }

        $user->update([
            'is_verified' => true,
            'verification_code' => null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'message' => 'Akun berhasil diverifikasi.',
            'token' => $token
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\SanitizerService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // ⭐ Sanitizar SOLO texto libre
        $validated['name'] = SanitizerService::clean($validated['name']);
        $validated['email'] = SanitizerService::clean($validated['email']);

        // ❌ NUNCA sanitizar password

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Usuario registrado. Revisa tu email para verificar la cuenta.'
        ]);
    }
    public function login(Request $request)
    {




        $email = (string) $request->email;
        $key = Str::lower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Demasiados intentos. Intenta más tarde.'
            ], 429);
        }

        RateLimiter::hit($key, 60); // 60 segundos



        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // ⭐ Sanitizar email
        $validated['email'] = SanitizerService::clean($validated['email']);

        // ❌ NUNCA sanitizar password

        $user = User::where('email', $validated['email'])->first();
        if (!$user->hasVerifiedEmail()) {
            return response()->json(['error' => 'Debes verificar tu email antes de iniciar sesión'], 403);
        }
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}

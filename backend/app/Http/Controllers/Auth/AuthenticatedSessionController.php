<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        // Validate credentials
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to authenticate the user
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // If authentication is successful, generate the token
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response with token
        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            // 'token_type' => 'Bearer',
            // 'user' => $user
        ]);
    }

    

    //     public function store(LoginRequest $request): Response
    // {
    //     // Memastikan pengguna terautentikasi menggunakan LoginRequest
    //     $request->authenticate();

    //     // Regenerasi sesi untuk menghindari serangan session fixation
    //     $request->session()->regenerate();

    //     // Kembalikan respons tanpa konten, yang menandakan login berhasil
    //     return response()->noContent();
    // }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}

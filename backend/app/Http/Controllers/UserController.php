<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Function to get signed in user`s data
    public function getSignedInUser()
    {
        $user = Auth::user();

        return response()->json($user);
    }

    public function getUserRole()
    {
        $user = Auth::user();

        if (! $user) {
            Log::warning('Akses tanpa autentikasi ke /user/role');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        Log::info('Role pengguna:', ['user_id' => $user->id, 'role' => $user->role]);

        return response()->json([
            'role' => (int) $user->role
        ]);
    }
}

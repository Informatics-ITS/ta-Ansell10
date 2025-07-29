<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     */

    public function store(UserRegisterRequest $request): JsonResponse
    {
        // Handle image upload if exists
        $profile_pic_url = null;
        if ($request->hasFile('image')) {
            $profile_pic = $request->file('image');
            $profile_pic_name = date('ymdhis') . '_' . Str::random(6) . '.' . $profile_pic->extension();
            $profile_pic_path = $profile_pic->storeAs('profile_pic', $profile_pic_name, 'public');
            $profile_pic_url = asset('storage/' . $profile_pic_path);
        }

        // Prepare user data
        $userData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            // Store image URL if available
            'image'    => $profile_pic_url,
            // Store the role value (1 for Perawat, 2 for Personal)
            'role'     => $request->role,  // Make sure role is passed from the form
        ];

        // Log the incoming role
        Log::info('User registration request:', ['role' => $request->role]);

        // 4) Create the user in the database
        $user = User::create($userData);

        // Trigger event after registration
        event(new Registered($user));
        
        // Log the user in after successful registration
        Auth::login($user);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
        ], 201);
    }

}

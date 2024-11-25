<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $file = $request->file('profile_picture');
        $name = $file->hashName();

        $user = User::create([
            ...$request->except('profile_picture'),
            'profile_picture' => $name
        ]);
        Storage::put("avatars/{$user->id}", $file);

        $token = $user->createToken('access_token')->plainTextToken;
        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;

        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => 'User registered successfully'
        ]);
    }
    public function login(LoginUserRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            $message = 'Mobile & password does not match with our records';
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => $message
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('access_token')->plainTextToken;

        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;

        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => 'User logged in successfully'
        ]);
    }
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 1,
            'data' => [],
            'message' => 'User logged out successfully'
        ]);
    }
}

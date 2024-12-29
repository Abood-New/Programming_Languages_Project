<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $profilePictureName = null;
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $profilePictureName = $file->hashName();
        }

        $verificationCode = random_int(100000, 999999);

        $userData = $request->only([
            'first_name',
            'last_name',
            'address',
            'phone',
            'password',
            'role'
        ]);
        $userData['profile_picture'] = $profilePictureName;
        $userData['verification_code'] = $verificationCode;

        $user = User::create($userData);

        // Step 3: Store Profile Picture (if uploaded)
        if ($profilePictureName) {
            $file->storeAs("users/{$user->id}", $profilePictureName);
        }

        // Step 4: Generate Access Token
        $token = $user->createToken('API TOKEN')->plainTextToken;

        // event(new UserRegistered($user, $verificationCode));

        return response()->json([
            'status' => 1,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'User registered successfully',
        ], 201);
    }
    public function login(LoginUserRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only(['phone', 'password']))) {
            $message = 'Phone number & password does not match with our records';
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => $message
            ], 401);
        }

        $user = User::where('phone', $request->phone)->first();

        $token = $user->createToken('API TOKEN', [$user->role])->plainTextToken;

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
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 1,
            'data' => [],
            'message' => 'User logged out successfully'
        ]);
    }
    public function verifyCode(Request $request): JsonResponse
    {
        $phone = auth()->user()->phone;
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $user = User::where('phone', $phone)
            ->where('verification_code', $request->code)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid verification code.',
            ], 400);
        }

        // Mark the user as verified
        $user->update(['verification_code' => null, 'phone_verified_at' => now()]);

        return response()->json([
            'status' => 1,
            'message' => 'Phone number verified successfully.',
        ]);
    }
}

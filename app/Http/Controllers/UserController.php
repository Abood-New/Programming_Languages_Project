<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updateProfile(UpdateUserRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete('users/' . $user->id . '/' . $user->profile_picture);
            }
            $request->file('profile_picture')->store('users/' . $user->id, 'public');
            $validated['profile_picture'] = $request->file('profile_picture')->hashName();
        }

        $user->update($validated);

        $user->profile_picture_url = asset('storage/users/' . $user->id . '/' . $user->profile_picture);

        return response()->json([
            'status' => 1,
            'data' => $user,
            'message' => "User Updated successfully"
        ]);
    }
}

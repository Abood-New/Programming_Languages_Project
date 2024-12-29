<?php
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function updateProfile(UpdateUserRequest $request)
    {
        $user = auth()->user();

        $user->update($request->validated());

        return response()->json([
            'status' => 1,
            'data' => $user,
            'message' => "User Updated successfully"
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'sometimes|string|max:255'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Optional: Revoke all previous tokens (one device at a time)
        $user->tokens()->delete();

        $token = $user->createToken($request->device_name ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'success'     => true,
            'message'     => 'Login successful',
            'token'       => $token,
            'token_type'  => 'Bearer',
            'user'        => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'username'  => $user->username,
                'user_type' => $user->user_type,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }

        // Token already invalid or user not authenticated
        return response()->json([
            'success' => false,
            'message' => 'Already logged out or invalid token'
        ], 401);
    }
}

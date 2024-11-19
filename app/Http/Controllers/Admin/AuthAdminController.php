<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthAdminController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => "required|email",
                'password' => "required"
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => __('messages.Invalid email or password'),
                    'status' => 401
                ], 401);
            }

            // Check if the user is an admin
            if ($user->role !== 'admin') {
                return response()->json([
                    'message' => __('messages.Unauthorized - only admins can access this route'),
                    'status' => 403
                ], 403);
            }

            // Create token only for admin role
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => $user->role,
                'status' => 200,
                'data' => $user,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => __('messages.Error: :error', ['error' => $e->getMessage()]),
                'status' => 500
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => __('messages.Successfully logged out')], 200);
    }
}

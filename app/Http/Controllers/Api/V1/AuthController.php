<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:admin,author' // Sesuaikan jika perlu
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Buat pengguna baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Generate token untuk pengguna yang baru terdaftar
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'token' => $token,
                    'user' => $user
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Login user dan generate JWT token
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized, invalid credentials',
                    'data' => null
                ], 401);
            }

            $user = Auth::user();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Logout user dan invalidate JWT token
    public function logout()
    {
        try {
            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Refresh JWT token
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Get logged-in user profile
    public function profile()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => Auth::user()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

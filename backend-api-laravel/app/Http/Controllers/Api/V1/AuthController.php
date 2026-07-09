<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/v1/register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'role'                 => 'student',
            'status'               => 'active',
            'last_communicated_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'       => 'success',
            'message'      => 'User registered successfully',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    // POST /api/v1/login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // BLACKLIST CHECK — block login if user is blacklisted
        if ($user->status === 'blacklisted') {
            if ($user->blacklist_expires_at && now()->greaterThan($user->blacklist_expires_at)) {
                // Ban has expired — reactivate automatically
                $user->status = 'active';
                $user->blacklist_expires_at = null;
                $user->last_communicated_at = now();
                $user->save();
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Your account has been blacklisted due to inactivity. Access is blocked until '
                        . $user->blacklist_expires_at->toDateTimeString(),
                ], 403);
            }
        }

        // Revoke old tokens and issue a fresh one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'       => 'success',
            'message'      => 'Login successful.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'status' => $user->status,
            ],
        ], 200);
    }

    // POST /api/v1/logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully.',
        ], 200);
    }

    // GET /api/v1/user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
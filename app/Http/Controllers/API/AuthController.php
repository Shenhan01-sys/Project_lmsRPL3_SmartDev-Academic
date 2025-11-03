<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,student,instructor,parent',
        ]);

        $ValidatedEmail = strtolower($validated['email']);
        $ValidatedName = ucfirst($validated['name']);

        $user = User::create([
            'name' => $ValidatedName,
            'email' => $ValidatedEmail,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Registration successful. Please create your profile.',
            'user' => $user,
            'next_step' => "Create {$validated['role']} profile via appropriate endpoint"
        ], 201);
    }

    /**
     * Authenticate a user and return a token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password yang diberikan salah'], 422);
        }

        // Revoke all old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load role-specific profile
        $profile = null;
        switch ($user->role) {
            case 'student':
                $profile = $user->student()->with('parent')->first();
                break;
            case 'instructor':
                $profile = $user->instructor()->with('courses')->first();
                break;
            case 'parent':
                $profile = $user->parentProfile()->with('students')->first();
                break;
        }

        return response()->json([
            'user' => $user,
            'profile' => $profile,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}

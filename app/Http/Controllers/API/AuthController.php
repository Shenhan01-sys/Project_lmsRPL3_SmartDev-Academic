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
     * Register a new user (DEPRECATED - Admin only via protected routes)
     *
     * @deprecated This endpoint is deprecated. Use specific registration endpoints:
     *             - POST /api/register-calon-siswa for student registration (public)
     *             - POST /api/v1/instructors for instructor creation (admin only)
     *             - POST /api/v1/parents for parent creation (admin only)
     *             - POST /api/v1/users for admin creation (admin only)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // This endpoint should not be used anymore
        // Keeping it for backward compatibility but return error
        return response()->json(
            [
                "message" =>
                    "This endpoint is deprecated. Please use the appropriate registration endpoint.",
                "available_endpoints" => [
                    "student_registration" =>
                        "POST /api/register-calon-siswa (public)",
                    "instructor_creation" =>
                        "POST /api/v1/instructors (admin only)",
                    "parent_creation" => "POST /api/v1/parents (admin only)",
                    "admin_creation" => "POST /api/v1/users (admin only)",
                ],
            ],
            410,
        ); // 410 Gone - indicates the endpoint is no longer available
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
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                ["message" => "Password yang diberikan salah"],
                422,
            );
        }

        // Revoke all old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken("auth_token")->plainTextToken;

        // Load role-specific profile
        $profile = null;
        switch ($user->role) {
            case "student":
                $profile = $user->student()->with("parent")->first();
                break;
            case "instructor":
                $profile = $user->instructor()->with("courses")->first();
                break;
            case "parent":
                $profile = $user->parentProfile()->with("students")->first();
                break;
        }

        return response()->json(
            [
                "user" => $user,
                "profile" => $profile,
                "access_token" => $token,
                "token_type" => "Bearer",
            ],
            200,
        );
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

        return response()->json(["message" => "Successfully logged out"], 200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

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
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register new user (DEPRECATED)",
     *     description="This endpoint is deprecated. Use role-specific registration endpoints instead.",
     *     tags={"Authentication"},
     *     deprecated=true,
     *     @OA\Response(
     *         response=410,
     *         description="Endpoint deprecated"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     description="Authenticate user and return access token. Supports all roles: student, instructor, parent, admin, calon_siswa",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Admin User"),
     *                 @OA\Property(property="email", type="string", example="admin@example.com"),
     *                 @OA\Property(property="role", type="string", example="admin")
     *             ),
     *             @OA\Property(property="profile", type="object", nullable=true, description="Role-specific profile data"),
     *             @OA\Property(property="access_token", type="string", example="1|abc123..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password yang diberikan salah")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     description="Invalidate the current user's token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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

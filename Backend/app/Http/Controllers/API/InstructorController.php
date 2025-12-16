<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class InstructorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/instructors",
     *     tags={"Instructors"},
     *     summary="Get all instructors",
     *     description="Retrieve a list of all instructors with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by instructor status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "resigned"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, email, specialization, or instructor code",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $query = Instructor::with("user");

        // Filter by status
        if ($request->has("status")) {
            $query->where("status", $request->status);
        }

        // Search by name or specialization
        if ($request->has("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("full_name", "like", "%$search%")
                    ->orWhere("email", "like", "%$search%")
                    ->orWhere("specialization", "like", "%$search%")
                    ->orWhere("instructor_code", "like", "%$search%");
            });
        }

        $instructors = $query->paginate($request->get("per_page", 15));

        return response()->json($instructors);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/instructors",
     *     tags={"Instructors"},
     *     summary="Create new instructor",
     *     description="Create a new instructor record and associated user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"instructor_code", "full_name", "email", "password"},
     *             @OA\Property(property="instructor_code", type="string", example="INS001"),
     *             @OA\Property(property="full_name", type="string", example="Dr. Jane Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="08123456789"),
     *             @OA\Property(property="specialization", type="string", example="Computer Science"),
     *             @OA\Property(property="education_level", type="string", example="PhD"),
     *             @OA\Property(property="experience_years", type="integer", example=5),
     *             @OA\Property(property="bio", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Instructor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Instructor created successfully"),
     *             @OA\Property(property="instructor", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "instructor_code" =>
                "required|string|unique:instructors,instructor_code",
            "full_name" => "required|string|max:255",
            "email" => "required|email|unique:instructors,email",
            "password" => "required|string|min:8",
            "phone" => "nullable|string|max:20",
            "specialization" => "nullable|string|max:255",
            "education_level" => "nullable|string|max:255",
            "experience_years" => "nullable|numeric|min:0",
            "bio" => "nullable|string",
        ]);

        DB::beginTransaction();
        try {
            // Create user account for login
            $user = User::create([
                "name" => $validated["full_name"],
                "email" => $validated["email"],
                "password" => Hash::make($validated["password"]),
                "role" => "instructor",
            ]);

            // Create instructor profile
            $instructor = Instructor::create([
                "user_id" => $user->id,
                "instructor_code" => $validated["instructor_code"],
                "full_name" => $validated["full_name"],
                "email" => $validated["email"],
                "phone" => $validated["phone"] ?? null,
                "specialization" => $validated["specialization"] ?? null,
                "education_level" => $validated["education_level"] ?? null,
                "experience_years" => $validated["experience_years"] ?? 0,
                "bio" => $validated["bio"] ?? null,
                "status" => "active",
            ]);

            DB::commit();

            return response()->json(
                [
                    "message" => "Instructor created successfully",
                    "instructor" => $instructor->load("user"),
                ],
                201,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    "message" => "Failed to create instructor",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/instructors/{id}",
     *     tags={"Instructors"},
     *     summary="Get instructor details",
     *     description="Get detailed information about a specific instructor",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Instructor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Instructor not found")
     * )
     */
    public function show(string $id)
    {
        $instructor = Instructor::with(["user", "courses"])->findOrFail($id);

        return response()->json($instructor);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/instructors/{id}",
     *     tags={"Instructors"},
     *     summary="Update instructor",
     *     description="Update an existing instructor record",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Instructor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="specialization", type="string"),
     *             @OA\Property(property="education_level", type="string"),
     *             @OA\Property(property="experience_years", type="integer"),
     *             @OA\Property(property="bio", type="string"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "resigned"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Instructor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Instructor updated successfully"),
     *             @OA\Property(property="instructor", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Instructor not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, string $id)
    {
        $instructor = Instructor::findOrFail($id);

        $validated = $request->validate([
            "full_name" => "sometimes|string|max:255",
            "email" => "sometimes|email|unique:instructors,email," . $id,
            "phone" => "nullable|string|max:20",
            "specialization" => "nullable|string|max:255",
            "education_level" => "nullable|string|max:255",
            "experience_years" => "nullable|numeric|min:0",
            "bio" => "nullable|string",
            "status" => "sometimes|in:active,inactive,resigned",
        ]);

        $instructor->update($validated);

        // Update user email if changed
        if (
            isset($validated["email"]) &&
            $instructor->user->email !== $validated["email"]
        ) {
            $instructor->user->update(["email" => $validated["email"]]);
        }

        return response()->json([
            "message" => "Instructor updated successfully",
            "instructor" => $instructor->load("user"),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/instructors/{id}",
     *     tags={"Instructors"},
     *     summary="Delete instructor",
     *     description="Delete an instructor record and associated user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Instructor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Instructor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Instructor deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Instructor not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(string $id)
    {
        $instructor = Instructor::findOrFail($id);

        DB::beginTransaction();
        try {
            // This will cascade delete the user account
            $instructor->user->delete();

            DB::commit();

            return response()->json([
                "message" => "Instructor deleted successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    "message" => "Failed to delete instructor",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/instructors/{id}/courses",
     *     tags={"Instructors"},
     *     summary="Get instructor courses",
     *     description="Get all courses taught by an instructor",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Instructor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="Instructor not found")
     * )
     */
    public function courses(string $id)
    {
        $instructor = Instructor::findOrFail($id);

        // Tambahkan withCount untuk menghitung enrollments
        $courses = $instructor
            ->courses()
            ->withCount([
                "enrollments as students_count" => function ($query) {
                    // Hitung hanya yang statusnya 'enrolled' atau 'active'
                    $query->whereIn("status", ["enrolled", "active"]);
                },
            ])
            ->get();

        return response()->json($courses);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/instructors/{id}/active-courses",
     *     tags={"Instructors"},
     *     summary="Get instructor active courses",
     *     description="Get all active courses taught by an instructor",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Instructor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="Instructor not found")
     * )
     */
    public function activeCourses(string $id)
    {
        $instructor = Instructor::findOrFail($id);

        $courses = $instructor
            ->activeCourses()
            ->withCount([
                "enrollments as students_count" => function ($query) {
                    $query->whereIn("status", ["enrolled", "active"]);
                },
            ])
            ->get();

        return response()->json($courses);
    }
}

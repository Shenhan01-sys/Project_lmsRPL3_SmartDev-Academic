<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use OpenApi\Annotations as OA;

class AssignmentController extends Controller
{
    use AuthorizesRequests;

    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/assignments",
     *     summary="Get all assignments",
     *     description="Retrieve assignments based on user role. Admin sees all, instructor sees their courses, student sees enrolled courses",
     *     tags={"Assignments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Quiz Mingguan 1"),
     *                 @OA\Property(property="description", type="string", example="Kerjakan quiz dengan teliti"),
     *                 @OA\Property(property="due_date", type="string", format="date-time", example="2025-12-31 23:59:59"),
     *                 @OA\Property(property="max_score", type="number", example=100),
     *                 @OA\Property(property="status", type="string", example="published")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin bisa lihat semua assignments
                $assignments = Assignment::with("course")->get();
            } elseif ($user->role === "instructor") {
                // Instructor hanya bisa lihat assignments dari course yang dia ajar
                $assignments = Assignment::with("course")
                    ->whereHas("course", function ($query) use ($user) {
                        $query->where("instructor_id", $user->instructor->id);
                    })
                    ->get();
            } elseif ($user->role === "student") {
                // Student hanya bisa lihat assignments dari course yang dia ikuti
                $enrolledCourseIds = $this->enrollmentService->getEnrolledCourseIds(
                    $user->student->id,
                );
                $assignments = Assignment::with("course")
                    ->whereIn("course_id", $enrolledCourseIds)
                    ->get();
            } else {
                $assignments = collect();
            }

            return response()->json($assignments);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving assignments",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/v1/assignments",
     *     summary="Create new assignment",
     *     description="Create a new assignment for a course (Instructor/Admin only)",
     *     tags={"Assignments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id", "title", "description", "due_date", "max_score"},
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Quiz Mingguan 1"),
     *             @OA\Property(property="description", type="string", example="Kerjakan quiz dengan teliti"),
     *             @OA\Property(property="due_date", type="string", format="date-time", example="2025-12-31 23:59:59"),
     *             @OA\Property(property="max_score", type="number", example=100),
     *             @OA\Property(property="status", type="string", example="published", enum={"draft", "published", "closed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Assignment created successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "course_id" => "required|exists:courses,id",
            "title" => "required|string|max:255",
            "description" => "required|string",
            "due_date" => "nullable|date",
        ]);

        $course = Course::find($validated["course_id"]);
        $user = $request->user();
        if (!$user->instructor || $user->instructor->id !== $course->instructor_id) {
            return response()->json(
                [
                    "message" => "You are not authorized to create assignments for this course.",
                ],
                403,
            );
        }

        try {
            $assignment = Assignment::create($validated);
            return response()->json($assignment, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating assignment",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/v1/assignments/{id}",
     *     summary="Get assignment details",
     *     description="Get detailed information about a specific assignment",
     *     tags={"Assignments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Quiz Mingguan 1"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="due_date", type="string", format="date-time"),
     *             @OA\Property(property="max_score", type="number", example=100),
     *             @OA\Property(property="status", type="string", example="published"),
     *             @OA\Property(property="course", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found"
     *     )
     * )
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\Response
     */
    public function show(Assignment $assignment)
    {
        $user = Auth::user();

        // Validasi: Student hanya bisa lihat assignment dari course yang dia ikuti
        if ($user->role === "student") {
            if (
                !$this->enrollmentService->isStudentEnrolledInCourse(
                    $user->student->id,
                    $assignment->course_id,
                )
            ) {
                return response()->json(
                    [
                        "message" =>
                            "You are not enrolled in the course for this assignment.",
                        "error" => "ENROLLMENT_REQUIRED",
                    ],
                    403,
                );
            }
        }

        // Instructor hanya bisa lihat assignment dari course yang dia ajar
        if ($user->role === "instructor") {
            if ($assignment->course->instructor_id !== $user->instructor->id) {
                return response()->json(
                    [
                        "message" =>
                            "You are not authorized to view this assignment.",
                        "error" => "UNAUTHORIZED",
                    ],
                    403,
                );
            }
        }

        $assignment->load("course", "submissions.student");
        return response()->json($assignment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/v1/assignments/{id}",
     *     summary="Update assignment",
     *     description="Update an existing assignment (Instructor/Admin only)",
     *     tags={"Assignments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Quiz Mingguan 1"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="due_date", type="string", format="date-time"),
     *             @OA\Property(property="max_score", type="number", example=100),
     *             @OA\Property(property="status", type="string", example="published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment updated successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            "course_id" => "sometimes|required|exists:courses,id",
            "title" => "sometimes|required|string|max:255",
            "description" => "sometimes|required|string",
            "due_date" => "nullable|date",
        ]);

        try {
            $assignment->update($validated);
            return response()->json($assignment);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating assignment",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/v1/assignments/{id}",
     *     summary="Delete assignment",
     *     description="Delete an assignment (Instructor/Admin only)",
     *     tags={"Assignments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Assignment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found"
     *     )
     * )
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Assignment $assignment)
    {
        try {
            $assignment->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting assignment",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

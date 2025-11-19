<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/enrollments",
     *     tags={"Enrollments"},
     *     summary="Get all enrollments",
     *     description="Retrieve a list of all enrollments with student and course details",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="student_id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index()
    {
        try {
            $enrollments = Enrollment::with([
                "student.user",
                "course.instructor",
            ])->get();
            return response()->json($enrollments);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving enrollments",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/enrollments",
     *     tags={"Enrollments"},
     *     summary="Enroll a student in a course",
     *     description="Create a new enrollment for a student in a specific course",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "course_id"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enrollment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="enrolled_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "student_id" => "required|exists:students,id",
            "course_id" => [
                "required",
                "exists:courses,id",
                Rule::unique("enrollments")->where(function ($query) use (
                    $request,
                ) {
                    return $query->where("student_id", $request->student_id);
                }),
            ],
        ]);

        try {
            $enrollment = Enrollment::create($validated);
            return response()->json($enrollment, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating enrollment",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Get enrollment by ID",
     *     description="Retrieve a specific enrollment with student and course details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Enrollment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Enrollment not found")
     * )
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(["student.user", "course.instructor"]);
        return response()->json($enrollment);
    }

    /**
     * @OA\Put(
     *     path="/api/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Update enrollment (not typically supported)",
     *     description="Enrollments are generally not updated, only created or deleted",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Enrollment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Method not allowed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment updates are not typically supported.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        // Generally, enrollments are not updated. They are created or deleted.
        // If an update is needed, validation rules would be similar to store.
        return response()->json(
            ["message" => "Enrollment updates are not typically supported."],
            405,
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Delete enrollment",
     *     description="Remove a student's enrollment from a course",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Enrollment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Enrollment deleted successfully"
     *     ),
     *     @OA\Response(response=404, description="Enrollment not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Enrollment $enrollment)
    {
        try {
            $enrollment->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting enrollment",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

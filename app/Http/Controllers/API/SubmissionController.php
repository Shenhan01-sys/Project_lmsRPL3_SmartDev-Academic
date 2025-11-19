<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class SubmissionController extends Controller
{
    use AuthorizesRequests;

    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * @OA\Get(
     *     path="/api/submissions",
     *     tags={"Submissions"},
     *     summary="Get all submissions",
     *     description="Retrieve a list of all assignment submissions",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="assignment_id", type="integer", example=1),
     *                 @OA\Property(property="student_id", type="integer", example=1),
     *                 @OA\Property(property="file_path", type="string", example="/uploads/submission.pdf"),
     *                 @OA\Property(property="grade", type="number", example=85.5),
     *                 @OA\Property(property="feedback", type="string", example="Good work!"),
     *                 @OA\Property(property="submitted_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index()
    {
        try {
            $submissions = Submission::with([
                "assignment",
                "student.user",
            ])->get();
            return response()->json($submissions);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving submissions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/submissions",
     *     tags={"Submissions"},
     *     summary="Submit an assignment",
     *     description="Create a new assignment submission (student must be enrolled in course)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"assignment_id"},
     *             @OA\Property(property="assignment_id", type="integer", example=1),
     *             @OA\Property(property="file_path", type="string", example="/uploads/submission.pdf"),
     *             @OA\Property(property="grade", type="number", example=85.5),
     *             @OA\Property(property="feedback", type="string", example="Good work!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Submission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="assignment_id", type="integer", example=1),
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="file_path", type="string", example="/uploads/submission.pdf"),
     *             @OA\Property(property="submitted_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not enrolled in course or not a student"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        // Get current user and their student profile
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(
                ["message" => "You must be a student to submit assignments."],
                403,
            );
        }

        $validated = $request->validate([
            "assignment_id" => [
                "required",
                "exists:assignments,id",
                Rule::unique("submissions")->where(function ($query) use (
                    $student,
                ) {
                    return $query->where("student_id", $student->id);
                }),
            ],
            "file_path" => "nullable|string",
            "grade" => "nullable|numeric",
            "feedback" => "nullable|string",
        ]);

        // VALIDASI: Pastikan student enrolled di course dari assignment ini
        if (
            !$this->enrollmentService->isStudentEnrolledInAssignmentCourse(
                $student->id,
                $validated["assignment_id"],
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

        // Auto-assign student_id from authenticated user's student profile
        $validated["student_id"] = $student->id;

        try {
            $submission = Submission::create($validated);
            $submission->load("assignment", "student.user");
            return response()->json($submission, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating submission",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/submissions/{id}",
     *     tags={"Submissions"},
     *     summary="Get submission by ID",
     *     description="Retrieve a specific submission with assignment and student details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Submission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="assignment_id", type="integer", example=1),
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="file_path", type="string", example="/uploads/submission.pdf"),
     *             @OA\Property(property="grade", type="number", example=85.5),
     *             @OA\Property(property="feedback", type="string", example="Good work!"),
     *             @OA\Property(property="submitted_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Submission not found")
     * )
     */
    public function show(Submission $submission)
    {
        // Authorization check via policy
        $this->authorize("view", $submission);

        $submission->load(["assignment", "student.user"]);
        return response()->json($submission);
    }

    /**
     * @OA\Put(
     *     path="/api/submissions/{id}",
     *     tags={"Submissions"},
     *     summary="Update submission",
     *     description="Update an existing assignment submission",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Submission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="assignment_id", type="integer", example=1),
     *             @OA\Property(property="file_path", type="string", example="/uploads/submission_updated.pdf"),
     *             @OA\Property(property="grade", type="number", example=90.0),
     *             @OA\Property(property="feedback", type="string", example="Excellent work!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="assignment_id", type="integer", example=1),
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="file_path", type="string", example="/uploads/submission_updated.pdf"),
     *             @OA\Property(property="grade", type="number", example=90.0),
     *             @OA\Property(property="feedback", type="string", example="Excellent work!")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized or not enrolled"),
     *     @OA\Response(response=404, description="Submission not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, Submission $submission)
    {
        // Authorization check via policy
        $this->authorize("update", $submission);

        $validated = $request->validate([
            "assignment_id" => [
                "sometimes",
                "required",
                "exists:assignments,id",
                Rule::unique("submissions")
                    ->ignore($submission->id)
                    ->where(function ($query) use ($submission) {
                        return $query->where(
                            "student_id",
                            $submission->student_id,
                        );
                    }),
            ],
            "file_path" => "nullable|string",
            "grade" => "nullable|numeric",
            "feedback" => "nullable|string",
        ]);

        // Jika assignment_id diubah, validasi enrollment di course baru
        if (
            isset($validated["assignment_id"]) &&
            $validated["assignment_id"] !== $submission->assignment_id
        ) {
            if (
                !$this->enrollmentService->isStudentEnrolledInAssignmentCourse(
                    $submission->student_id,
                    $validated["assignment_id"],
                )
            ) {
                return response()->json(
                    [
                        "message" =>
                            "Student is not enrolled in the course for the new assignment.",
                        "error" => "ENROLLMENT_REQUIRED",
                    ],
                    403,
                );
            }
        }

        try {
            $submission->update($validated);
            $submission->load(["assignment", "student.user"]);
            return response()->json($submission);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating submission",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/submissions/{id}",
     *     tags={"Submissions"},
     *     summary="Delete submission",
     *     description="Remove an assignment submission",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Submission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Submission deleted successfully"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Submission not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Submission $submission)
    {
        // Authorization check via policy
        $this->authorize("delete", $submission);

        try {
            $submission->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting submission",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

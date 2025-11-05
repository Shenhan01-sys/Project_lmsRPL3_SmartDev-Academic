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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\Response
     */
    public function show(Submission $submission)
    {
        // Authorization check via policy
        $this->authorize("view", $submission);

        $submission->load(["assignment", "student.user"]);
        return response()->json($submission);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\Response
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

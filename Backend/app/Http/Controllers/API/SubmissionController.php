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
use Illuminate\Support\Facades\Storage;

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
     *     path="/api/v1/submissions",
     *     tags={"Submissions"},
     *     summary="Get all submissions",
     *     description="Retrieve a list of all assignment submissions",
     *     security={{"bearerAuth":{}}},
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
            $user = auth()->user();

            $query = Submission::with([
                "assignment",
                "enrollment.student.user", // ✅ Fix relasi
            ]);

            // Filter berdasarkan role
            if ($user->role === "student") {
                // Student hanya lihat submission sendiri
                $studentId = $user->student->id;
                $query->whereHas("enrollment", function ($q) use ($studentId) {
                    $q->where("student_id", $studentId);
                });
            } elseif ($user->role === "instructor") {
                // Instructor lihat submission dari course yang diajar
                $instructorId = $user->instructor->id;
                $query->whereHas("assignment.course", function ($q) use (
                    $instructorId,
                ) {
                    $q->where("instructor_id", $instructorId);
                });
            }
            // Admin bisa lihat semua

            $submissions = $query->get();

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
     *     path="/api/v1/submissions",
     *     tags={"Submissions"},
     *     summary="Submit an assignment",
     *     description="Create a new assignment submission (student must be enrolled in course)",
     *     security={{"bearerAuth":{}}},
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

        // Validation: tambahkan support untuk file upload
        $validated = $request->validate([
            "assignment_id" => "required|exists:assignments,id",
            "file_path" => "nullable|string",
            "file" =>
                "nullable|file|mimes:pdf,doc,docx,zip,rar,ppt,pptx,txt|max:10240",
            "status" => "nullable|in:draft,submitted",
            "submit_now" => "boolean",
        ]);

        // Get assignment
        $assignment = \App\Models\Assignment::findOrFail(
            $validated["assignment_id"],
        );

        // VALIDASI: Pastikan student enrolled di course dari assignment ini
        $enrollment = \App\Models\Enrollment::where("student_id", $student->id)
            ->where("course_id", $assignment->course_id)
            ->where("status", "active")
            ->first();

        if (!$enrollment) {
            return response()->json(
                [
                    "message" =>
                        "You are not enrolled in the course for this assignment.",
                    "error" => "ENROLLMENT_REQUIRED",
                ],
                403,
            );
        }

        // Check if submission already exists
        $existingSubmission = Submission::where(
            "assignment_id",
            $validated["assignment_id"],
        )
            ->where("enrollment_id", $enrollment->id)
            ->first();

        try {
            $filePath = null;

            // Handle file upload
            if ($request->hasFile("file")) {
                $file = $request->file("file");
                $fileName = time() . "_" . $file->getClientOriginalName();

                // If updating existing submission, delete old file first
                if ($existingSubmission && $existingSubmission->file_path) {
                    if (
                        \Storage::disk("public")->exists(
                            $existingSubmission->file_path,
                        )
                    ) {
                        \Storage::disk("public")->delete(
                            $existingSubmission->file_path,
                        );
                    }
                }

                // Store file and save only relative path
                $filePath = $file->storeAs("submissions", $fileName, "public");
                // $filePath now contains: submissions/filename.pdf
            } elseif (isset($validated["file_path"])) {
                // Backward compatibility: terima file_path langsung
                $filePath = $validated["file_path"];
            } elseif ($existingSubmission) {
                // Keep existing file path if no new file uploaded
                $filePath = $existingSubmission->file_path;
            }

            // Check if past deadline when submitting (not draft)
            $isPastDeadline = false;
            $lateDays = 0;
            if (
                $request->boolean("submit_now", false) &&
                $assignment->due_date
            ) {
                $now = now();
                $dueDate = \Carbon\Carbon::parse($assignment->due_date);
                if ($now->isAfter($dueDate)) {
                    $isPastDeadline = true;
                    $lateDays = $now->diffInDays($dueDate);
                }
            }

            if ($existingSubmission) {
                // Update existing submission - allow resubmission before deadline
                $existingSubmission->file_path = $filePath;
                $existingSubmission->is_late = $isPastDeadline;
                $existingSubmission->late_days = $lateDays;

                // If submit_now is true, mark as submitted
                if ($request->boolean("submit_now", false)) {
                    $existingSubmission->status = "submitted";
                    $existingSubmission->submitted_at = now();
                } else {
                    $existingSubmission->status = "draft";
                }

                $existingSubmission->save();
                $submission = $existingSubmission;
            } else {
                // Create new submission
                $submissionData = [
                    "assignment_id" => $validated["assignment_id"],
                    "enrollment_id" => $enrollment->id,
                    "file_path" => $filePath,
                    "status" => "draft",
                    "is_late" => $isPastDeadline,
                    "late_days" => $lateDays,
                ];

                $submission = Submission::create($submissionData);

                // If submit_now is true, mark as submitted
                if ($request->boolean("submit_now", false)) {
                    $submission->markAsSubmitted();
                }
            }

            $submission->load(["assignment", "enrollment.student.user"]);

            $message = $existingSubmission
                ? ($submission->isSubmitted()
                    ? "Assignment resubmitted successfully"
                    : "Draft updated successfully")
                : ($submission->isSubmitted()
                    ? "Assignment submitted successfully"
                    : "Draft saved successfully");

            return response()->json(
                [
                    "message" => $message,
                    "submission" => $submission,
                ],
                201,
            );
        } catch (\Exception $e) {
            \Log::error("Submission creation error: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());

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
     *     path="/api/v1/submissions/{id}",
     *     tags={"Submissions"},
     *     summary="Get submission by ID",
     *     description="Retrieve a specific submission with assignment and student details",
     *     security={{"bearerAuth":{}}},
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
     *     path="/api/v1/submissions/{id}",
     *     tags={"Submissions"},
     *     summary="Update submission",
     *     description="Update an existing assignment submission",
     *     security={{"bearerAuth":{}}},
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

        $user = $request->user();
        $isInstructor = $user->role === "instructor" || $user->role === "admin";

        // Students can only update if NOT graded (allow resubmission before grading)
        if (!$isInstructor && $submission->status === "graded") {
            return response()->json(
                [
                    "message" =>
                        "You cannot update a submission that has been graded.",
                ],
                403,
            );
        }

        $validated = $request->validate([
            "file_path" => "nullable|string",
            "file" =>
                "nullable|file|mimes:pdf,doc,docx,zip,rar,ppt,pptx|max:10240", // ← TAMBAH INI
            "grade" => "nullable|numeric|min:0",
            "feedback" => "nullable|string",
            "status" => "nullable|in:draft,submitted,graded,returned",
            "submit_now" => "boolean",
        ]);

        try {
            // Handle file upload jika ada
            if ($request->hasFile("file")) {
                // Hapus file lama jika ada
                if (
                    $submission->file_path &&
                    \Storage::disk("public")->exists($submission->file_path)
                ) {
                    \Storage::disk("public")->delete($submission->file_path);
                }

                $file = $request->file("file");
                $fileName = time() . "_" . $file->getClientOriginalName();
                // Store file and save only relative path
                $submission->file_path = $file->storeAs(
                    "submissions",
                    $fileName,
                    "public",
                );
                // $submission->file_path now contains: submissions/filename.pdf
            }

            // Students can update file_path
            if (isset($validated["file_path"]) && !$request->hasFile("file")) {
                $submission->file_path = $validated["file_path"];
            }

            // Instructors can update grade and feedback
            if ($isInstructor) {
                if (isset($validated["grade"])) {
                    $submission->grade = $validated["grade"];
                }
                if (isset($validated["feedback"])) {
                    $submission->feedback = $validated["feedback"];
                }
                if (isset($validated["status"])) {
                    $submission->status = $validated["status"];
                }
            }

            // If submit_now is true, mark as submitted
            if ($request->boolean("submit_now", false) && !$isInstructor) {
                $submission->markAsSubmitted();
            }

            $submission->save();
            $submission->load(["assignment", "enrollment.student.user"]);

            return response()->json(
                [
                    "message" => "Submission updated successfully",
                    "submission" => $submission,
                ],
                200,
            );
        } catch (\Exception $e) {
            \Log::error("Submission update error: " . $e->getMessage());

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
     *     path="/api/v1/submissions/{id}",
     *     tags={"Submissions"},
     *     summary="Delete submission",
     *     description="Remove an assignment submission",
     *     security={{"bearerAuth":{}}},
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

        $user = Auth::user();

        // Students can only delete draft submissions
        if ($user->role === "student" && $submission->status !== "draft") {
            return response()->json(
                [
                    "message" =>
                        "You can only delete draft submissions. Cannot delete submitted or graded work.",
                ],
                403,
            );
        }

        try {
            $submission->delete();
            return response()->json(
                [
                    "message" => "Submission deleted successfully",
                ],
                200,
            );
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

    /**
     * Submit a draft submission
     * @OA\Post(
     *     path="/api/v1/submissions/{id}/submit",
     *     tags={"Submissions"},
     *     summary="Submit a draft submission",
     *     description="Mark a draft submission as submitted (student only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Submission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submission submitted successfully"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized or already submitted"),
     *     @OA\Response(response=404, description="Submission not found")
     * )
     */
    public function submit(Submission $submission)
    {
        $this->authorize("update", $submission);

        if ($submission->status !== "draft") {
            return response()->json(
                [
                    "message" => "This submission has already been submitted.",
                ],
                422,
            );
        }

        try {
            $submission->markAsSubmitted();
            $submission->load(["assignment", "enrollment.student.user"]);

            return response()->json([
                "message" => "Assignment submitted successfully",
                "submission" => $submission,
                "is_late" => $submission->is_late,
                "late_days" => $submission->late_days,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error submitting assignment",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get submissions for a specific assignment
     * @OA\Get(
     *     path="/api/v1/assignments/{assignmentId}/submissions",
     *     tags={"Submissions"},
     *     summary="Get all submissions for an assignment",
     *     description="Get all submissions for a specific assignment (instructor/admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="assignmentId",
     *         in="path",
     *         required=true,
     *         description="Assignment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getByAssignment($assignmentId)
    {
        $user = Auth::user();
        $assignment = \App\Models\Assignment::findOrFail($assignmentId);

        // Check authorization
        if ($user->role === "instructor") {
            if ($assignment->course->instructor_id !== $user->instructor->id) {
                return response()->json(
                    [
                        "message" =>
                            "You are not authorized to view submissions for this assignment.",
                    ],
                    403,
                );
            }
        } elseif ($user->role !== "admin") {
            return response()->json(["message" => "Unauthorized"], 403);
        }

        try {
            $submissions = Submission::where("assignment_id", $assignmentId)
                ->with(["enrollment.student.user", "assignment"])
                ->get();

            return response()->json([
                "assignment" => $assignment,
                "submissions" => $submissions,
                "statistics" => [
                    "total" => $submissions->count(),
                    "submitted" => $submissions
                        ->whereIn("status", ["submitted", "graded", "returned"])
                        ->count(),
                    "graded" => $submissions
                        ->where("status", "graded")
                        ->count(),
                    "late" => $submissions->where("is_late", true)->count(),
                    "draft" => $submissions->where("status", "draft")->count(),
                ],
            ]);
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
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * @OA\Get(
     * path="/api/v1/assignments",
     * tags={"Assignments"},
     * summary="Get all assignments",
     * description="Retrieve assignments based on user role",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if ($user->role === "admin") {
                $assignments = Assignment::with("course")->get();
            } elseif ($user->role === "instructor") {
                $assignments = Assignment::with("course")
                    ->whereHas("course", function ($query) use ($user) {
                        $query->where("instructor_id", $user->instructor->id);
                    })
                    ->get();
            } elseif ($user->role === "student") {
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
     * @OA\Post(
     * path="/api/v1/assignments",
     * tags={"Assignments"},
     * summary="Create new assignment",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"course_id", "title", "description", "due_date", "max_score"},
     * @OA\Property(property="course_id", type="integer"),
     * @OA\Property(property="title", type="string"),
     * @OA\Property(property="description", type="string"),
     * @OA\Property(property="due_date", type="string", format="date-time"),
     * @OA\Property(property="max_score", type="number")
     * )
     * ),
     * @OA\Response(response=201, description="Created"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "course_id" => "required|exists:courses,id",
            "title" => "required|string|max:255",
            "description" => "required|string",
            "due_date" => "nullable|date",
            "max_score" => "required|numeric|min:0|max:100",
            "content_path" => "nullable|string",
            "file" =>
                "nullable|file|mimes:pdf,doc,docx,zip,rar,ppt,pptx,txt,jpg,jpeg,png|max:10240",
        ]);

        $course = Course::find($validated["course_id"]);
        $user = $request->user();

        // --- PERBAIKAN LOGIKA OTORISASI ---
        $isAuthorized = false;

        if ($user->role === "admin") {
            $isAuthorized = true;
        } elseif ($user->role === "instructor" && $user->instructor) {
            // Izinkan jika instruktur yang login adalah pemilik course ini
            if ($user->instructor->id === $course->instructor_id) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return response()->json(
                [
                    "message" =>
                        "You are not authorized to create assignments for this course.",
                ],
                403,
            );
        }

        try {
            // Handle file upload
            $contentPath = null;

            if ($request->hasFile("file")) {
                $file = $request->file("file");
                $fileName = time() . "_" . $file->getClientOriginalName();

                // Store file in public disk under 'assignments' folder
                $contentPath = $file->storeAs(
                    "assignments",
                    $fileName,
                    "public",
                );
                // $contentPath now contains: assignments/filename.pdf
            } elseif (isset($validated["content_path"])) {
                // Backward compatibility: accept content_path directly
                $contentPath = $validated["content_path"];
            }

            $validated["content_path"] = $contentPath;
            $validated["status"] = "published"; // Default status

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
     * @OA\Get(
     * path="/api/v1/assignments/{id}",
     * tags={"Assignments"},
     * summary="Get assignment details",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Success"),
     * @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Assignment $assignment)
    {
        $user = Auth::user();

        // Validasi Akses Student
        if ($user->role === "student") {
            if (
                !$this->enrollmentService->isStudentEnrolledInCourse(
                    $user->student->id,
                    $assignment->course_id,
                )
            ) {
                return response()->json(
                    [
                        "message" => "You are not enrolled in this course.",
                        "error" => "ENROLLMENT_REQUIRED",
                    ],
                    403,
                );
            }
        }

        // Validasi Akses Instructor
        if ($user->role === "instructor") {
            if ($assignment->course->instructor_id !== $user->instructor->id) {
                return response()->json(
                    ["message" => "Unauthorized.", "error" => "UNAUTHORIZED"],
                    403,
                );
            }
        }

        // --- PERBAIKAN LOAD RELASI ---
        // Load submissions -> enrollment -> student -> user (agar dapat nama)
        $assignment->load(["course", "submissions.enrollment.student.user"]);

        return response()->json($assignment);
    }

    /**
     * @OA\Put(
     * path="/api/v1/assignments/{id}",
     * tags={"Assignments"},
     * summary="Update assignment",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            "course_id" => "sometimes|required|exists:courses,id",
            "title" => "sometimes|required|string|max:255",
            "description" => "sometimes|required|string",
            "due_date" => "nullable|date",
            "max_score" => "sometimes|numeric",
            "content_path" => "nullable|string",
            "file" =>
                "nullable|file|mimes:pdf,doc,docx,zip,rar,ppt,pptx,txt,jpg,jpeg,png|max:10240",
        ]);

        // Cek Otorisasi Update (Admin atau Pemilik Course)
        $user = Auth::user();
        $isAuthorized =
            $user->role === "admin" ||
            ($user->role === "instructor" &&
                $user->instructor &&
                $user->instructor->id === $assignment->course->instructor_id);

        if (!$isAuthorized) {
            return response()->json(["message" => "Unauthorized"], 403);
        }

        try {
            // Handle file upload
            if ($request->hasFile("file")) {
                $file = $request->file("file");
                $fileName = time() . "_" . $file->getClientOriginalName();

                // Delete old file if exists
                if ($assignment->content_path) {
                    if (
                        \Storage::disk("public")->exists(
                            $assignment->content_path,
                        )
                    ) {
                        \Storage::disk("public")->delete(
                            $assignment->content_path,
                        );
                    }
                }

                // Store new file
                $contentPath = $file->storeAs(
                    "assignments",
                    $fileName,
                    "public",
                );
                $validated["content_path"] = $contentPath;
            } elseif (isset($validated["content_path"])) {
                // Accept content_path directly if provided
                $validated["content_path"] = $validated["content_path"];
            }

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
     * @OA\Delete(
     * path="/api/v1/assignments/{id}",
     * tags={"Assignments"},
     * summary="Delete assignment",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Deleted")
     * )
     */
    public function destroy(Assignment $assignment)
    {
        // Cek Otorisasi Delete
        $user = Auth::user();
        $isAuthorized =
            $user->role === "admin" ||
            ($user->role === "instructor" &&
                $user->instructor &&
                $user->instructor->id === $assignment->course->instructor_id);

        if (!$isAuthorized) {
            return response()->json(["message" => "Unauthorized"], 403);
        }

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

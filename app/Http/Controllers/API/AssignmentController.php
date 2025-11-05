<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

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
        if ($request->user()->id !== $course->instructor_id) {
            return response()->json(
                [
                    "message" =>
                        "You are not authorized to create assignments for this course.",
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

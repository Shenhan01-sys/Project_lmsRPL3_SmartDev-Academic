<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceSessionController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/attendance-sessions",
     *     tags={"Attendance Sessions"},
     *     summary="Get all attendance sessions",
     *     description="Retrieve attendance sessions with filtering (students see only sessions for enrolled courses)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filter by course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"open", "closed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance sessions retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", AttendanceSession::class);

        try {
            $user = Auth::user();
            $query = AttendanceSession::with(["course", "attendanceRecords"]);

            // Filtering by course
            if ($request->has("course_id")) {
                $query->where("course_id", $request->course_id);
            }

            // Filtering by status
            if ($request->has("status")) {
                $query->where("status", $request->status);
            }

            // Authorization: Students only see sessions for their enrolled courses
            if ($user->role === "student") {
                $enrolledCourseIds = Enrollment::where(
                    "student_id",
                    $user->student->id,
                )
                    ->where("status", "active")
                    ->pluck("course_id");

                $query->whereIn("course_id", $enrolledCourseIds);
            } elseif ($user->role === "instructor") {
                // Instructor only sees sessions for their courses
                $instructorCourseIds = Course::where(
                    "instructor_id",
                    $user->instructor->id,
                )->pluck("id");

                $query->whereIn("course_id", $instructorCourseIds);
            }

            // Sorting
            $sessions = $query->orderBy("deadline", "desc")->paginate(20);

            return response()->json($sessions);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving attendance sessions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions",
     *     tags={"Attendance Sessions"},
     *     summary="Create new attendance session",
     *     description="Create a new attendance session for a course",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id", "session_name", "deadline"},
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="session_name", type="string", example="Week 1 Attendance"),
     *             @OA\Property(property="status", type="string", enum={"open", "closed"}, example="open"),
     *             @OA\Property(property="deadline", type="string", format="date-time"),
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Attendance session created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize("create", AttendanceSession::class);

        $validated = $request->validate([
            "course_id" => "required|exists:courses,id",
            "session_name" => "required|string|max:255",
            "status" => "nullable|in:open,closed",
            "deadline" => "required|date|after:now",
            "start_time" => "nullable|date",
            "end_time" => "nullable|date|after:start_time",
        ]);

        try {
            $user = Auth::user();
            $course = Course::findOrFail($validated["course_id"]);

            // Authorization: Check if user can create session for this course
            $this->authorize("createForCourse", [
                AttendanceSession::class,
                $course,
            ]);

            // Set default status if not provided
            if (!isset($validated["status"])) {
                $validated["status"] = "open";
            }

            $session = AttendanceSession::create($validated);
            $session->load(["course", "attendanceRecords"]);

            return response()->json($session, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating attendance session",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendance-sessions/{id}",
     *     tags={"Attendance Sessions"},
     *     summary="Get attendance session by ID",
     *     description="Retrieve a specific attendance session with attendance records and summary",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance session retrieved successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(AttendanceSession $attendanceSession)
    {
        $this->authorize("view", $attendanceSession);

        try {
            $attendanceSession->load([
                "course",
                "attendanceRecords.enrollment.student.user",
            ]);

            // Add summary to response
            $summary = $attendanceSession->getAttendanceSummary();
            $percentage = $attendanceSession->getAttendancePercentage();

            $response = $attendanceSession->toArray();
            $response["summary"] = $summary;
            $response["attendance_percentage"] = $percentage;

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving attendance session",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/attendance-sessions/{id}",
     *     tags={"Attendance Sessions"},
     *     summary="Update attendance session",
     *     description="Update an existing attendance session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"session_name", "deadline"},
     *             @OA\Property(property="session_name", type="string", example="Week 1 Attendance Updated"),
     *             @OA\Property(property="status", type="string", enum={"open", "closed"}),
     *             @OA\Property(property="deadline", type="string", format="date-time"),
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance session updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(
        Request $request,
        AttendanceSession $attendanceSession,
    ) {
        $this->authorize("update", $attendanceSession);

        $validated = $request->validate([
            "session_name" => "required|string|max:255",
            "status" => "nullable|in:open,closed",
            "deadline" => "required|date",
            "start_time" => "nullable|date",
            "end_time" => "nullable|date|after:start_time",
        ]);

        try {
            $attendanceSession->update($validated);
            $attendanceSession->load(["course", "attendanceRecords"]);

            return response()->json($attendanceSession);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating attendance session",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/attendance-sessions/{id}",
     *     tags={"Attendance Sessions"},
     *     summary="Delete attendance session",
     *     description="Remove an attendance session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance session deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Attendance session deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(AttendanceSession $attendanceSession)
    {
        $this->authorize("delete", $attendanceSession);

        try {
            $attendanceSession->delete();

            return response()->json(
                [
                    "message" => "Attendance session deleted successfully",
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting attendance session",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions/{id}/open",
     *     tags={"Attendance Sessions"},
     *     summary="Open attendance session",
     *     description="Change attendance session status to open",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance session opened successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Attendance session opened successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function openSession(AttendanceSession $attendanceSession)
    {
        $this->authorize("update", $attendanceSession);

        try {
            $attendanceSession->open();
            $attendanceSession->load(["course", "attendanceRecords"]);

            return response()->json([
                "message" => "Attendance session opened successfully",
                "data" => $attendanceSession,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error opening attendance session",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions/{id}/close",
     *     tags={"Attendance Sessions"},
     *     summary="Close attendance session",
     *     description="Change attendance session status to closed",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance session closed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Attendance session closed successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function closeSession(AttendanceSession $attendanceSession)
    {
        $this->authorize("update", $attendanceSession);

        try {
            $attendanceSession->close();
            $attendanceSession->load(["course", "attendanceRecords"]);

            return response()->json([
                "message" => "Attendance session closed successfully",
                "data" => $attendanceSession,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error closing attendance session",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions/{id}/auto-mark-absent",
     *     tags={"Attendance Sessions"},
     *     summary="Auto-mark absent",
     *     description="Automatically mark students as absent if they haven't checked in after deadline",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Auto-marked absent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Auto-marked absent successfully"),
     *             @OA\Property(property="marked_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Session has not expired yet"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function autoMarkAbsent(AttendanceSession $attendanceSession)
    {
        $this->authorize("update", $attendanceSession);

        try {
            if (!$attendanceSession->hasExpired()) {
                return response()->json(
                    [
                        "message" =>
                            "Cannot auto-mark absent. Session has not expired yet.",
                    ],
                    400,
                );
            }

            $markedCount = $attendanceSession->autoMarkAbsent();

            return response()->json([
                "message" => "Auto-marked absent successfully",
                "marked_count" => $markedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error auto-marking absent",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendance-sessions/{id}/summary",
     *     tags={"Attendance Sessions"},
     *     summary="Get attendance session summary",
     *     description="Retrieve attendance summary statistics for a specific session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session summary retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="session_id", type="integer", example=1),
     *             @OA\Property(property="session_name", type="string", example="Week 1 Attendance"),
     *             @OA\Property(property="summary", type="object"),
     *             @OA\Property(property="attendance_percentage", type="number", format="float", example=85.5)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Attendance session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getSessionSummary(AttendanceSession $attendanceSession)
    {
        $this->authorize("view", $attendanceSession);

        try {
            $summary = $attendanceSession->getAttendanceSummary();
            $percentage = $attendanceSession->getAttendancePercentage();

            return response()->json([
                "session_id" => $attendanceSession->id,
                "session_name" => $attendanceSession->session_name,
                "summary" => $summary,
                "attendance_percentage" => $percentage,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving session summary",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendance-sessions/course/{courseId}",
     *     tags={"Attendance Sessions"},
     *     summary="Get course attendance sessions",
     *     description="Retrieve all attendance sessions for a specific course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course sessions retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized or not enrolled"),
     *     @OA\Response(response=404, description="Course not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getSessionsByCourse($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Authorization: Check if user has access to this course
            if ($user->role === "student") {
                $isEnrolled = Enrollment::where(
                    "student_id",
                    $user->student->id,
                )
                    ->where("course_id", $courseId)
                    ->where("status", "active")
                    ->exists();

                if (!$isEnrolled) {
                    return response()->json(
                        [
                            "message" => "You are not enrolled in this course",
                        ],
                        403,
                    );
                }
            } elseif ($user->role === "instructor") {
                if ($course->instructor_id !== $user->instructor->id) {
                    return response()->json(
                        [
                            "message" =>
                                "You are not the instructor of this course",
                        ],
                        403,
                    );
                }
            }

            $sessions = AttendanceSession::with(["attendanceRecords"])
                ->where("course_id", $courseId)
                ->orderBy("deadline", "desc")
                ->get();

            // Add summary for each session
            $sessions->map(function ($session) {
                $session->summary = $session->getAttendanceSummary();
                $session->attendance_percentage = $session->getAttendancePercentage();
                return $session;
            });

            return response()->json($sessions);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving course sessions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendance-sessions/course/{courseId}/active",
     *     tags={"Attendance Sessions"},
     *     summary="Get active course attendance sessions",
     *     description="Retrieve all active (open) attendance sessions for a specific course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active course sessions retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized or not enrolled"),
     *     @OA\Response(response=404, description="Course not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getActiveSessionsByCourse($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Authorization: Check if user has access to this course
            if ($user->role === "student") {
                $isEnrolled = Enrollment::where(
                    "student_id",
                    $user->student->id,
                )
                    ->where("course_id", $courseId)
                    ->where("status", "active")
                    ->exists();

                if (!$isEnrolled) {
                    return response()->json(
                        [
                            "message" => "You are not enrolled in this course",
                        ],
                        403,
                    );
                }
            } elseif ($user->role === "instructor") {
                if ($course->instructor_id !== $user->instructor->id) {
                    return response()->json(
                        [
                            "message" =>
                                "You are not the instructor of this course",
                        ],
                        403,
                    );
                }
            }

            $sessions = AttendanceSession::with(["attendanceRecords"])
                ->where("course_id", $courseId)
                ->active()
                ->orderBy("deadline", "asc")
                ->get();

            // Add summary for each session
            $sessions->map(function ($session) {
                $session->summary = $session->getAttendanceSummary();
                $session->attendance_percentage = $session->getAttendancePercentage();
                return $session;
            });

            return response()->json($sessions);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving active sessions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

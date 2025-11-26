<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRecordController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions/{sessionId}/check-in",
     *     tags={"Attendance Records"},
     *     summary="Student check-in for attendance",
     *     description="Allow students to check in for an open attendance session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check-in successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Check-in successful"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Session closed or expired"),
     *     @OA\Response(response=403, description="Not a student or not enrolled"),
     *     @OA\Response(response=404, description="Session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function checkIn($sessionId)
    {
        try {
            $user = Auth::user();

            if ($user->role !== "student") {
                return response()->json(
                    [
                        "message" => "Only students can check in",
                    ],
                    403,
                );
            }

            $session = AttendanceSession::findOrFail($sessionId);

            // Check if session is still open
            if ($session->status !== "open") {
                return response()->json(
                    [
                        "message" => "Attendance session is not open",
                    ],
                    400,
                );
            }

            // Check if session has expired
            if ($session->hasExpired()) {
                return response()->json(
                    [
                        "message" => "Attendance session has expired",
                    ],
                    400,
                );
            }

            // Get student enrollment
            $enrollment = Enrollment::where("student_id", $user->student->id)
                ->where("course_id", $session->course_id)
                ->where("status", "active")
                ->first();

            if (!$enrollment) {
                return response()->json(
                    [
                        "message" => "You are not enrolled in this course",
                    ],
                    403,
                );
            }

            // Check if record already exists
            $record = AttendanceRecord::where("enrollment_id", $enrollment->id)
                ->where("attendance_session_id", $sessionId)
                ->first();

            if ($record) {
                if ($record->status === "present") {
                    return response()->json(
                        [
                            "message" => "You have already checked in",
                            "data" => $record,
                        ],
                        200,
                    );
                }

                // Update existing record
                $record->checkIn();
            } else {
                // Create new record
                $record = AttendanceRecord::create([
                    "enrollment_id" => $enrollment->id,
                    "attendance_session_id" => $sessionId,
                    "status" => "present",
                    "check_in_time" => now(),
                ]);
            }

            $record->load(["enrollment.student.user", "attendanceSession"]);

            return response()->json(
                [
                    "message" => "Check-in successful",
                    "data" => $record,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error checking in",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions/{sessionId}/sick-leave",
     *     tags={"Attendance Records"},
     *     summary="Request sick leave",
     *     description="Student requests sick leave for an attendance session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Flu and fever"),
     *             @OA\Property(property="attachment", type="string", example="/uploads/sick-note.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sick leave request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sick leave request submitted"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not a student or not enrolled"),
     *     @OA\Response(response=404, description="Session not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function requestSickLeave(Request $request, $sessionId)
    {
        $validated = $request->validate([
            "notes" => "required|string|max:1000",
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== "student") {
                return response()->json(
                    [
                        "message" => "Only students can request sick leave",
                    ],
                    403,
                );
            }

            $session = AttendanceSession::findOrFail($sessionId);

            // Get student enrollment
            $enrollment = Enrollment::where("student_id", $user->student->id)
                ->where("course_id", $session->course_id)
                ->where("status", "active")
                ->first();

            if (!$enrollment) {
                return response()->json(
                    [
                        "message" => "You are not enrolled in this course",
                    ],
                    403,
                );
            }

            // Check if record already exists
            $record = AttendanceRecord::where("enrollment_id", $enrollment->id)
                ->where("attendance_session_id", $sessionId)
                ->first();

            if ($record) {
                // Update existing record
                $record->update([
                    "status" => "sick",
                    "notes" => $validated["notes"],
                ]);
            } else {
                // Create new record
                $record = AttendanceRecord::create([
                    "enrollment_id" => $enrollment->id,
                    "attendance_session_id" => $sessionId,
                    "status" => "sick",
                    "notes" => $validated["notes"],
                ]);
            }

            $record->load(["enrollment.student.user", "attendanceSession"]);

            return response()->json(
                [
                    "message" => "Sick leave request submitted successfully",
                    "data" => $record,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error submitting sick leave request",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-sessions/{sessionId}/permission",
     *     tags={"Attendance Records"},
     *     summary="Request permission/izin",
     *     description="Student requests permission to be absent for an attendance session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Family emergency"),
     *             @OA\Property(property="attachment", type="string", example="/uploads/permission-letter.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permission request submitted"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not a student or not enrolled"),
     *     @OA\Response(response=404, description="Session not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function requestPermission(Request $request, $sessionId)
    {
        $validated = $request->validate([
            "notes" => "required|string|max:1000",
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== "student") {
                return response()->json(
                    [
                        "message" => "Only students can request permission",
                    ],
                    403,
                );
            }

            $session = AttendanceSession::findOrFail($sessionId);

            // Get student enrollment
            $enrollment = Enrollment::where("student_id", $user->student->id)
                ->where("course_id", $session->course_id)
                ->where("status", "active")
                ->first();

            if (!$enrollment) {
                return response()->json(
                    [
                        "message" => "You are not enrolled in this course",
                    ],
                    403,
                );
            }

            // Check if record already exists
            $record = AttendanceRecord::where("enrollment_id", $enrollment->id)
                ->where("attendance_session_id", $sessionId)
                ->first();

            if ($record) {
                // Update existing record
                $record->update([
                    "status" => "permission",
                    "notes" => $validated["notes"],
                ]);
            } else {
                // Create new record
                $record = AttendanceRecord::create([
                    "enrollment_id" => $enrollment->id,
                    "attendance_session_id" => $sessionId,
                    "status" => "permission",
                    "notes" => $validated["notes"],
                ]);
            }

            $record->load(["enrollment.student.user", "attendanceSession"]);

            return response()->json(
                [
                    "message" => "Permission request submitted successfully",
                    "data" => $record,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error submitting permission request",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Instructor manually mark attendance for a student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sessionId
     * @param  int  $enrollmentId
     * @return \Illuminate\Http\Response
     */
    public function markAttendance(Request $request, $sessionId, $enrollmentId)
    {
        $validated = $request->validate([
            "status" => "required|in:present,absent,sick,permission",
            "notes" => "nullable|string|max:1000",
        ]);

        try {
            $user = Auth::user();
            $session = AttendanceSession::findOrFail($sessionId);

            // Authorization: Check if user is instructor of this course
            $this->authorize("update", $session);

            $enrollment = Enrollment::findOrFail($enrollmentId);

            // Check if enrollment belongs to the session's course
            if ($enrollment->course_id !== $session->course_id) {
                return response()->json(
                    [
                        "message" =>
                            "Enrollment does not belong to this course",
                    ],
                    400,
                );
            }

            // Check if record already exists
            $record = AttendanceRecord::where("enrollment_id", $enrollmentId)
                ->where("attendance_session_id", $sessionId)
                ->first();

            $data = [
                "status" => $validated["status"],
                "notes" => $validated["notes"] ?? null,
            ];

            if ($validated["status"] === "present") {
                $data["check_in_time"] = now();
            }

            if ($record) {
                $record->update($data);
            } else {
                $data["enrollment_id"] = $enrollmentId;
                $data["attendance_session_id"] = $sessionId;
                $record = AttendanceRecord::create($data);
            }

            $record->load(["enrollment.student.user", "attendanceSession"]);

            return response()->json(
                [
                    "message" => "Attendance marked successfully",
                    "data" => $record,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error marking attendance",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Approve sick/permission attendance record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $recordId
     * @return \Illuminate\Http\Response
     */
    public function approveAttendance(Request $request, $recordId)
    {
        $validated = $request->validate([
            "notes" => "nullable|string|max:1000",
        ]);

        try {
            $user = Auth::user();
            $record = AttendanceRecord::findOrFail($recordId);
            $record->load(["attendanceSession", "enrollment"]);

            // Authorization: Check if user is instructor of this course
            $this->authorize("update", $record->attendanceSession);

            if (!in_array($record->status, ["sick", "permission"])) {
                return response()->json(
                    [
                        "message" =>
                            "Only sick or permission records can be approved",
                    ],
                    400,
                );
            }

            $record->approve($user->id, $validated["notes"] ?? null);
            $record->load([
                "enrollment.student.user",
                "attendanceSession",
                "reviewer",
            ]);

            return response()->json(
                [
                    "message" => "Attendance record approved successfully",
                    "data" => $record,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error approving attendance record",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Reject sick/permission attendance record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $recordId
     * @return \Illuminate\Http\Response
     */
    public function rejectAttendance(Request $request, $recordId)
    {
        $validated = $request->validate([
            "notes" => "required|string|max:1000",
        ]);

        try {
            $user = Auth::user();
            $record = AttendanceRecord::findOrFail($recordId);
            $record->load(["attendanceSession", "enrollment"]);

            // Authorization: Check if user is instructor of this course
            $this->authorize("update", $record->attendanceSession);

            if (!in_array($record->status, ["sick", "permission"])) {
                return response()->json(
                    [
                        "message" =>
                            "Only sick or permission records can be rejected",
                    ],
                    400,
                );
            }

            $record->reject($user->id, $validated["notes"]);
            $record->load([
                "enrollment.student.user",
                "attendanceSession",
                "reviewer",
            ]);

            return response()->json(
                [
                    "message" => "Attendance record rejected successfully",
                    "data" => $record,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error rejecting attendance record",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance-records/bulk-mark",
     *     tags={"Attendance Records"},
     *     summary="Bulk mark attendance",
     *     description="Mark attendance for multiple students at once (Instructor/Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"records"},
     *             @OA\Property(
     *                 property="records",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="enrollment_id", type="integer", example=1),
     *                     @OA\Property(property="attendance_session_id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", enum={"present", "absent", "sick", "permission"}),
     *                     @OA\Property(property="notes", type="string", example="Optional notes")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bulk attendance marked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bulk attendance marked successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="count", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function bulkMarkAttendance(Request $request)
    {
        $validated = $request->validate([
            "records" => "required|array",
            "records.*.enrollment_id" =>
                "required|integer|exists:enrollments,id",
            "records.*.status" => "required|in:present,absent,sick,permission",
            "records.*.notes" => "nullable|string|max:1000",
        ]);

        try {
            $user = Auth::user();
            $session = AttendanceSession::findOrFail($sessionId);

            // Authorization: Check if user is instructor of this course
            $this->authorize("update", $session);

            $results = [];

            DB::beginTransaction();

            foreach ($validated["records"] as $recordData) {
                $enrollment = Enrollment::findOrFail(
                    $recordData["enrollment_id"],
                );

                // Check if enrollment belongs to the session's course
                if ($enrollment->course_id !== $session->course_id) {
                    continue;
                }

                $record = AttendanceRecord::where(
                    "enrollment_id",
                    $recordData["enrollment_id"],
                )
                    ->where("attendance_session_id", $sessionId)
                    ->first();

                $data = [
                    "status" => $recordData["status"],
                    "notes" => $recordData["notes"] ?? null,
                ];

                if ($recordData["status"] === "present") {
                    $data["check_in_time"] = now();
                }

                if ($record) {
                    $record->update($data);
                } else {
                    $data["enrollment_id"] = $recordData["enrollment_id"];
                    $data["attendance_session_id"] = $sessionId;
                    $record = AttendanceRecord::create($data);
                }

                $results[] = $record;
            }

            DB::commit();

            return response()->json(
                [
                    "message" => "Bulk attendance marked successfully",
                    "count" => count($results),
                    "data" => $results,
                ],
                200,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    "message" => "Error bulk marking attendance",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get student attendance history for a course.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function getStudentAttendanceHistory($studentId, $courseId)
    {
        try {
            $user = Auth::user();

            // Authorization: Student can only view their own history, instructors can view their course students
            if ($user->role === "student" && $user->student->id != $studentId) {
                return response()->json(
                    [
                        "message" =>
                            "You can only view your own attendance history",
                    ],
                    403,
                );
            }

            if ($user->role === "instructor") {
                $course = Course::findOrFail($courseId);
                if ($course->instructor_id !== $user->instructor->id) {
                    return response()->json(
                        [
                            "message" =>
                                "You can only view attendance for your courses",
                        ],
                        403,
                    );
                }
            }

            $enrollment = Enrollment::where("student_id", $studentId)
                ->where("course_id", $courseId)
                ->first();

            if (!$enrollment) {
                return response()->json(
                    [
                        "message" => "Student is not enrolled in this course",
                    ],
                    404,
                );
            }

            $records = AttendanceRecord::with(["attendanceSession", "reviewer"])
                ->where("enrollment_id", $enrollment->id)
                ->orderBy("created_at", "desc")
                ->get();

            return response()->json($records);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving attendance history",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendance-sessions/{sessionId}/records",
     *     tags={"Attendance Records"},
     *     summary="Get session attendance records",
     *     description="Retrieve all attendance records for a specific session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         description="Attendance Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session records retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Session not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getSessionRecords($sessionId)
    {
        try {
            $user = Auth::user();
            $session = AttendanceSession::findOrFail($sessionId);

            // Authorization
            $this->authorize("view", $session);

            $records = AttendanceRecord::with([
                "enrollment.student.user",
                "reviewer",
            ])
                ->where("attendance_session_id", $sessionId)
                ->orderBy("created_at", "asc")
                ->get();

            return response()->json($records);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving session records",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get attendance records needing review for a course.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function getRecordsNeedingReview($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Authorization: Only instructor can review
            if (
                $user->role === "instructor" &&
                $course->instructor_id !== $user->instructor->id
            ) {
                return response()->json(
                    [
                        "message" =>
                            "You can only review attendance for your courses",
                    ],
                    403,
                );
            }

            if ($user->role !== "instructor" && $user->role !== "admin") {
                return response()->json(
                    [
                        "message" => "Only instructors can review attendance",
                    ],
                    403,
                );
            }

            $sessionIds = AttendanceSession::where(
                "course_id",
                $courseId,
            )->pluck("id");

            $records = AttendanceRecord::with([
                "enrollment.student.user",
                "attendanceSession",
            ])
                ->whereIn("attendance_session_id", $sessionIds)
                ->needsReview()
                ->orderBy("created_at", "asc")
                ->get();

            return response()->json($records);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving records needing review",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get student attendance statistics for a course.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function getStudentAttendanceStats($studentId, $courseId)
    {
        try {
            $user = Auth::user();

            // Authorization: Student can only view their own stats
            if ($user->role === "student" && $user->student->id != $studentId) {
                return response()->json(
                    [
                        "message" =>
                            "You can only view your own attendance statistics",
                    ],
                    403,
                );
            }

            if ($user->role === "instructor") {
                $course = Course::findOrFail($courseId);
                if ($course->instructor_id !== $user->instructor->id) {
                    return response()->json(
                        [
                            "message" =>
                                "You can only view statistics for your courses",
                        ],
                        403,
                    );
                }
            }

            $enrollment = Enrollment::where("student_id", $studentId)
                ->where("course_id", $courseId)
                ->first();

            if (!$enrollment) {
                return response()->json(
                    [
                        "message" => "Student is not enrolled in this course",
                    ],
                    404,
                );
            }

            $records = AttendanceRecord::where(
                "enrollment_id",
                $enrollment->id,
            )->get();

            $stats = [
                "total_sessions" => $records->count(),
                "present" => $records->where("status", "present")->count(),
                "absent" => $records->where("status", "absent")->count(),
                "sick" => $records->where("status", "sick")->count(),
                "permission" => $records
                    ->where("status", "permission")
                    ->count(),
                "pending" => $records->where("status", "pending")->count(),
            ];

            $stats["attendance_percentage"] =
                $stats["total_sessions"] > 0
                    ? round(
                        ($stats["present"] / $stats["total_sessions"]) * 100,
                        2,
                    )
                    : 0;

            $stats["excused"] = $stats["sick"] + $stats["permission"];
            $stats["excused_percentage"] =
                $stats["total_sessions"] > 0
                    ? round(
                        (($stats["present"] + $stats["excused"]) /
                            $stats["total_sessions"]) *
                            100,
                        2,
                    )
                    : 0;

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving attendance statistics",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

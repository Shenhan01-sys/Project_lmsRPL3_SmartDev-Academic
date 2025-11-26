<?php

use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\AssignmentController;
use App\Http\Controllers\API\AttendanceRecordController;
use App\Http\Controllers\API\AttendanceSessionController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CertificateController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\CourseModuleController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ParentController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\InstructorController;
use App\Http\Controllers\API\SubmissionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\FileUploadController;

Route::get("/test", function () {
    return response()->json(["message" => "File api.php berhasil diakses!"]);
});
/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Login)
|--------------------------------------------------------------------------
|
| Rute-rute ini dapat diakses oleh siapa saja.
|
*/
Route::post("/login", [AuthController::class, "login"]);
Route::post("/forgot-password", [PasswordController::class, "forgotPassword"]);
Route::post("/reset-password", [PasswordController::class, "resetPassword"]);

// Registration routes for calon siswa
Route::post("/register-calon-siswa", [
    App\Http\Controllers\API\RegistrationController::class,
    "registerCalonSiswa",
]);

// Routes that require authentication but not full user verification
Route::middleware("auth:sanctum")->group(function () {
    Route::post("/upload-documents", [
        App\Http\Controllers\API\RegistrationController::class,
        "uploadDocuments",
    ]);
    Route::get("/registration-status", [
        App\Http\Controllers\API\RegistrationController::class,
        "getRegistrationStatus",
    ]);
});

/*
|--------------------------------------------------------------------------
| Rute Terlindungi (Wajib Login / Punya Token)
|--------------------------------------------------------------------------
|
| Semua rute di dalam grup ini memerlukan token otentikasi yang valid.
|
*/
Route::middleware("auth:sanctum")->group(function () {
    // Rute untuk mendapatkan data user yang sedang login
    Route::get("/user", function (Request $request) {
        return $request->user();
    });

    // Rute untuk logout
    Route::post("/logout", [AuthController::class, "logout"]);

    // Rute untuk password management (user harus login)
    Route::post("/change-password", [
        PasswordController::class,
        "changePassword",
    ]);

    // Rute untuk file upload
    Route::prefix("upload")->group(function () {
        Route::post("/profile-photo", [
            FileUploadController::class,
            "uploadProfilePhoto",
        ]);
        Route::post("/material/{materialId}", [
            FileUploadController::class,
            "uploadMaterialFile",
        ]);
        Route::post("/assignment/{assignmentId}", [
            FileUploadController::class,
            "uploadAssignmentFile",
        ]);
        Route::post("/submission/{submissionId}", [
            FileUploadController::class,
            "uploadSubmissionFile",
        ]);
        Route::delete("/file", [FileUploadController::class, "deleteFile"]);
        Route::get("/file-info", [FileUploadController::class, "getFileInfo"]);
    });

    // Grup untuk semua rute CRUD resource dengan prefix v1
    Route::prefix("v1")->group(function () {
        // User management (Admin only, for managing admin accounts)
        Route::get("instructors", [
            UserController::class,
            "indexInstructor",
        ])->name("users.instructors");
        Route::apiResource("users", UserController::class);

        // Student management
        Route::apiResource("students", StudentController::class);
        Route::get("students/{student}/enrollments", [
            StudentController::class,
            "enrollments",
        ])->name("students.enrollments");
        Route::get("students/{student}/submissions", [
            StudentController::class,
            "submissions",
        ])->name("students.submissions");

        // Instructor management
        Route::apiResource("instructors", InstructorController::class);
        Route::get("instructors/{instructor}/courses", [
            InstructorController::class,
            "courses",
        ])->name("instructors.courses");
        Route::get("instructors/{instructor}/active-courses", [
            InstructorController::class,
            "activeCourses",
        ])->name("instructors.active-courses");

        // Parent management
        Route::apiResource("parents", ParentController::class);
        Route::get("parents/{parent}/students", [
            ParentController::class,
            "students",
        ])->name("parents.students");
        Route::get("parents/{parent}/active-students", [
            ParentController::class,
            "activeStudents",
        ])->name("parents.active-students");

        // Course & Academic routes
        Route::apiResource("courses", CourseController::class);
        Route::apiResource("enrollments", EnrollmentController::class);
        Route::apiResource("course-modules", CourseModuleController::class);
        Route::apiResource("materials", MaterialController::class);
        Route::apiResource("assignments", AssignmentController::class);
        Route::apiResource("submissions", SubmissionController::class);

        // Hybrid approach routes for discovery
        Route::get("course-modules/browse", [
            CourseModuleController::class,
            "browse",
        ])->name("course-modules.browse");
        Route::get("course-modules/my-modules", [
            CourseModuleController::class,
            "myModules",
        ])->name("course-modules.my");
        Route::get("materials/browse", [
            MaterialController::class,
            "browse",
        ])->name("materials.browse");
        Route::get("materials/my-materials", [
            MaterialController::class,
            "myMaterials",
        ])->name("materials.my");

        // Registration management routes (Admin only)
        Route::get("registrations/pending", [
            App\Http\Controllers\API\RegistrationController::class,
            "getPendingRegistrations",
        ])->name("registrations.pending");
        Route::get("registrations", [
            App\Http\Controllers\API\RegistrationController::class,
            "getAllRegistrations",
        ])->name("registrations.all");
        Route::post("registrations/{user}/approve", [
            App\Http\Controllers\API\RegistrationController::class,
            "approveRegistration",
        ])->name("registrations.approve");
        Route::post("registrations/{user}/reject", [
            App\Http\Controllers\API\RegistrationController::class,
            "rejectRegistration",
        ])->name("registrations.reject");

        // Routes khusus untuk grading
        Route::post("/grades/bulk", [
            App\Http\Controllers\API\GradeController::class,
            "bulkStore",
        ])->name("grades.bulk");
        Route::get("/grades/student", [
            App\Http\Controllers\API\GradeController::class,
            "getStudentGrades",
        ])->name("grades.student");
        Route::get("/grades/course", [
            App\Http\Controllers\API\GradeController::class,
            "getCourseGrades",
        ])->name("grades.course");

        // Routes untuk Grading System
        Route::apiResource(
            "grade-components",
            App\Http\Controllers\API\GradeComponentController::class,
        );
        Route::apiResource(
            "grades",
            App\Http\Controllers\API\GradeController::class,
        );

        // Announcement routes (custom routes BEFORE apiResource to prevent conflicts)

        // Custom announcement routes for filtering/scope
        Route::get("announcements/course/{courseId}", [
            AnnouncementController::class,
            "getCourseAnnouncements",
        ])->name("announcements.course");
        Route::get("announcements/global/list", [
            AnnouncementController::class,
            "getGlobalAnnouncements",
        ])->name("announcements.global");
        Route::get("announcements/active/list", [
            AnnouncementController::class,
            "getActiveAnnouncements",
        ])->name("announcements.active");

        // Custom announcement routes for status management
        Route::post("announcements/{announcement}/publish", [
            AnnouncementController::class,
            "publish",
        ])->name("announcements.publish");
        Route::post("announcements/{announcement}/archive", [
            AnnouncementController::class,
            "archive",
        ])->name("announcements.archive");

        // Standard CRUD routes for announcements
        Route::apiResource("announcements", AnnouncementController::class);

        // Notification routes (custom routes BEFORE apiResource to prevent conflicts)

        // Custom notification routes for filtering/scope
        Route::get("notifications/unread/list", [
            NotificationController::class,
            "getUnreadNotifications",
        ])->name("notifications.unread");
        Route::get("notifications/read/list", [
            NotificationController::class,
            "getReadNotifications",
        ])->name("notifications.read");
        Route::get("notifications/type/{type}", [
            NotificationController::class,
            "getByType",
        ])->name("notifications.by-type");
        Route::get("notifications/unread/count", [
            NotificationController::class,
            "getUnreadCount",
        ])->name("notifications.unread-count");

        // Custom notification routes for mark as read/unread
        Route::post("notifications/mark-all-read", [
            NotificationController::class,
            "markAllAsRead",
        ])->name("notifications.mark-all-read");
        Route::post("notifications/{notification}/mark-read", [
            NotificationController::class,
            "markAsRead",
        ])->name("notifications.mark-read");
        Route::post("notifications/{notification}/mark-unread", [
            NotificationController::class,
            "markAsUnread",
        ])->name("notifications.mark-unread");

        // Bulk operations for notifications
        Route::post("notifications/bulk-mark-read", [
            NotificationController::class,
            "bulkMarkAsRead",
        ])->name("notifications.bulk-mark-read");
        Route::post("notifications/bulk-delete", [
            NotificationController::class,
            "bulkDelete",
        ])->name("notifications.bulk-delete");
        Route::delete("notifications/delete-all-read", [
            NotificationController::class,
            "deleteAllRead",
        ])->name("notifications.delete-all-read");

        // Standard CRUD routes for notifications
        Route::apiResource(
            "notifications",
            NotificationController::class,
        )->only(["index", "show", "destroy"]);

        // Attendance Session routes (custom routes BEFORE apiResource)

        // Custom attendance session routes for specific course
        Route::get("attendance-sessions/course/{courseId}/all", [
            AttendanceSessionController::class,
            "getSessionsByCourse",
        ])->name("attendance-sessions.course-all");
        Route::get("attendance-sessions/course/{courseId}/active", [
            AttendanceSessionController::class,
            "getActiveSessionsByCourse",
        ])->name("attendance-sessions.course-active");

        // Custom attendance session routes for status management
        Route::post("attendance-sessions/{attendanceSession}/open", [
            AttendanceSessionController::class,
            "openSession",
        ])->name("attendance-sessions.open");
        Route::post("attendance-sessions/{attendanceSession}/close", [
            AttendanceSessionController::class,
            "closeSession",
        ])->name("attendance-sessions.close");
        Route::post(
            "attendance-sessions/{attendanceSession}/auto-mark-absent",
            [AttendanceSessionController::class, "autoMarkAbsent"],
        )->name("attendance-sessions.auto-mark-absent");

        // Custom attendance session routes for summary
        Route::get("attendance-sessions/{attendanceSession}/summary", [
            AttendanceSessionController::class,
            "getSessionSummary",
        ])->name("attendance-sessions.summary");

        // Standard CRUD routes for attendance sessions
        Route::apiResource(
            "attendance-sessions",
            AttendanceSessionController::class,
        );

        // Attendance Record routes (custom routes BEFORE apiResource if needed)

        // Student actions
        Route::post("attendance-records/check-in/{sessionId}", [
            AttendanceRecordController::class,
            "checkIn",
        ])->name("attendance-records.check-in");
        Route::post("attendance-records/sick-leave/{sessionId}", [
            AttendanceRecordController::class,
            "requestSickLeave",
        ])->name("attendance-records.sick-leave");
        Route::post("attendance-records/permission/{sessionId}", [
            AttendanceRecordController::class,
            "requestPermission",
        ])->name("attendance-records.permission");

        // Instructor actions
        Route::post("attendance-records/mark/{sessionId}/{enrollmentId}", [
            AttendanceRecordController::class,
            "markAttendance",
        ])->name("attendance-records.mark");
        Route::post("attendance-records/{recordId}/approve", [
            AttendanceRecordController::class,
            "approveAttendance",
        ])->name("attendance-records.approve");
        Route::post("attendance-records/{recordId}/reject", [
            AttendanceRecordController::class,
            "rejectAttendance",
        ])->name("attendance-records.reject");
        Route::post("attendance-records/bulk-mark/{sessionId}", [
            AttendanceRecordController::class,
            "bulkMarkAttendance",
        ])->name("attendance-records.bulk-mark");

        // Reporting and statistics
        Route::get(
            "attendance-records/student/{studentId}/course/{courseId}/history",
            [AttendanceRecordController::class, "getStudentAttendanceHistory"],
        )->name("attendance-records.student-history");
        Route::get("attendance-records/session/{sessionId}/records", [
            AttendanceRecordController::class,
            "getSessionRecords",
        ])->name("attendance-records.session-records");
        Route::get("attendance-records/course/{courseId}/needs-review", [
            AttendanceRecordController::class,
            "getRecordsNeedingReview",
        ])->name("attendance-records.needs-review");
        Route::get(
            "attendance-records/student/{studentId}/course/{courseId}/stats",
            [AttendanceRecordController::class, "getStudentAttendanceStats"],
        )->name("attendance-records.student-stats");

        // Certificate routes (custom routes BEFORE apiResource)

        // Public verification endpoints (no auth required)
        Route::get("certificates/verify/code/{certificateCode}", [
            CertificateController::class,
            "verify",
        ])
            ->name("certificates.verify-code")
            ->withoutMiddleware(["auth:sanctum"]);
        Route::get("certificates/verify/{certificate}", [
            CertificateController::class,
            "verifyByCertificate",
        ])
            ->name("certificates.verify-id")
            ->withoutMiddleware(["auth:sanctum"]);

        // Generate certificates
        Route::post("certificates/generate/{enrollmentId}", [
            CertificateController::class,
            "generate",
        ])->name("certificates.generate");
        Route::post("certificates/bulk-generate/{courseId}", [
            CertificateController::class,
            "bulkGenerate",
        ])->name("certificates.bulk-generate");

        // Revoke certificate
        Route::post("certificates/{certificate}/revoke", [
            CertificateController::class,
            "revoke",
        ])->name("certificates.revoke");

        // Reporting and filtering
        Route::get("certificates/student/{studentId}", [
            CertificateController::class,
            "getStudentCertificates",
        ])->name("certificates.student");
        Route::get("certificates/course/{courseId}", [
            CertificateController::class,
            "getCourseCertificates",
        ])->name("certificates.course");

        // Check eligibility
        Route::get("certificates/eligibility/{enrollmentId}", [
            CertificateController::class,
            "checkEligibility",
        ])->name("certificates.eligibility");

        // Standard CRUD routes for certificates (limited)
        Route::apiResource("certificates", CertificateController::class)->only([
            "index",
            "show",
            "destroy",
        ]);
    });
});

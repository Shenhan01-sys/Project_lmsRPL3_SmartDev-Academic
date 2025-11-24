<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class CertificateController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/certificates",
     *     tags={"Certificates"},
     *     summary="Get all certificates",
     *     description="Retrieve a list of certificates with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filter by course ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filter by student ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by certificate status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"issued", "revoked"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Certificate::class);

        try {
            $user = Auth::user();
            $query = Certificate::with(['enrollment.student.user', 'course', 'generator']);

            // Filtering
            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('student_id')) {
                $query->whereHas('enrollment', function($q) use ($request) {
                    $q->where('student_id', $request->student_id);
                });
            }

            // Authorization: Students only see their certificates
            if ($user->role === 'student') {
                $query->whereHas('enrollment', function($q) use ($user) {
                    $q->where('student_id', $user->student->id);
                });
            } elseif ($user->role === 'instructor') {
                // Instructor only sees certificates for their courses
                $instructorCourseIds = Course::where('instructor_id', $user->instructor->id)
                    ->pluck('id');

                $query->whereIn('course_id', $instructorCourseIds);
            } elseif ($user->role === 'parent') {
                // Parent sees certificates of their children
                $childrenIds = $user->parentProfile->students()->pluck('students.id');

                $query->whereHas('enrollment', function($q) use ($childrenIds) {
                    $q->whereIn('student_id', $childrenIds);
                });
            }

            // Sorting
            $certificates = $query->orderBy('issue_date', 'desc')
                ->paginate(20);

            return response()->json($certificates);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/certificates/{id}",
     *     tags={"Certificates"},
     *     summary="Get certificate details",
     *     description="Get detailed information about a specific certificate",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Certificate not found")
     * )
     */
    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        try {
            // Increment verification count
            $certificate->incrementVerificationCount();

            $certificate->load(['enrollment.student.user', 'course', 'generator']);

            return response()->json($certificate);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/certificates/{id}",
     *     tags={"Certificates"},
     *     summary="Delete certificate",
     *     description="Delete a certificate (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Certificate deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Certificate not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Certificate $certificate)
    {
        $this->authorize('delete', $certificate);

        try {
            $certificate->delete();

            return response()->json([
                'message' => 'Certificate deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/certificates/generate/{enrollmentId}",
     *     tags={"Certificates"},
     *     summary="Generate certificate",
     *     description="Generate a certificate for a specific enrollment",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enrollmentId",
     *         in="path",
     *         required=true,
     *         description="Enrollment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Certificate generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Certificate generated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Certificate already exists or not eligible"),
     *     @OA\Response(response=404, description="Enrollment not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function generate($enrollmentId)
    {
        try {
            $user = Auth::user();
            $enrollment = Enrollment::with(['student.user', 'course'])->findOrFail($enrollmentId);

            // Authorization
            $this->authorize('generate', [Certificate::class, $enrollment]);

            // Check if certificate already exists
            $existingCertificate = Certificate::where('enrollment_id', $enrollmentId)->first();
            if ($existingCertificate) {
                return response()->json([
                    'message' => 'Certificate already exists for this enrollment',
                    'data' => $existingCertificate
                ], 400);
            }

            // Check eligibility
            $eligibility = Certificate::checkEligibility($enrollment);

            if (!$eligibility['eligible']) {
                return response()->json([
                    'message' => 'Student is not eligible for certificate',
                    'errors' => $eligibility['errors'],
                    'eligibility' => $eligibility
                ], 400);
            }

            // Generate certificate code
            $certificateCode = Certificate::generateCertificateCode(
                $enrollment->course->course_code,
                now()->year
            );

            // Calculate grade letter
            $gradeLetter = Certificate::calculateGradeLetter($enrollment->final_grade);

            // Create certificate
            $certificate = Certificate::create([
                'enrollment_id' => $enrollmentId,
                'course_id' => $enrollment->course_id,
                'certificate_code' => $certificateCode,
                'final_grade' => $enrollment->final_grade,
                'attendance_percentage' => $eligibility['attendance_percentage'],
                'assignment_completion_rate' => $eligibility['assignment_completion_rate'],
                'grade_letter' => $gradeLetter,
                'issue_date' => now(),
                'generated_by' => $user->id,
                'status' => 'issued',
                'metadata' => [
                    'student_name' => $enrollment->student->user->name,
                    'course_name' => $enrollment->course->course_name,
                    'instructor_name' => $enrollment->course->instructor->user->name ?? null,
                ],
            ]);

            $certificate->load(['enrollment.student.user', 'course', 'generator']);

            return response()->json([
                'message' => 'Certificate generated successfully',
                'data' => $certificate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/certificates/bulk-generate/{courseId}",
     *     tags={"Certificates"},
     *     summary="Bulk generate certificates",
     *     description="Generate certificates for all eligible students in a course",
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
     *         description="Bulk generation completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bulk certificate generation completed"),
     *             @OA\Property(property="summary", type="object"),
     *             @OA\Property(property="results", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Course not found or no enrollments"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function bulkGenerate($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Authorization
            $this->authorize('bulkGenerate', [Certificate::class, $course]);

            $enrollments = Enrollment::with(['student.user'])
                ->where('course_id', $courseId)
                ->where('status', 'completed')
                ->get();

            if ($enrollments->isEmpty()) {
                return response()->json([
                    'message' => 'No completed enrollments found for this course'
                ], 404);
            }

            $results = [
                'success' => [],
                'failed' => [],
                'already_exists' => [],
            ];

            DB::beginTransaction();

            foreach ($enrollments as $enrollment) {
                // Check if certificate already exists
                $existingCertificate = Certificate::where('enrollment_id', $enrollment->id)->first();
                if ($existingCertificate) {
                    $results['already_exists'][] = [
                        'enrollment_id' => $enrollment->id,
                        'student_name' => $enrollment->student->user->name,
                        'certificate_id' => $existingCertificate->id,
                    ];
                    continue;
                }

                // Check eligibility
                $eligibility = Certificate::checkEligibility($enrollment);

                if (!$eligibility['eligible']) {
                    $results['failed'][] = [
                        'enrollment_id' => $enrollment->id,
                        'student_name' => $enrollment->student->user->name,
                        'errors' => $eligibility['errors'],
                    ];
                    continue;
                }

                // Generate certificate code
                $certificateCode = Certificate::generateCertificateCode(
                    $course->course_code,
                    now()->year
                );

                // Calculate grade letter
                $gradeLetter = Certificate::calculateGradeLetter($enrollment->final_grade);

                // Create certificate
                $certificate = Certificate::create([
                    'enrollment_id' => $enrollment->id,
                    'course_id' => $courseId,
                    'certificate_code' => $certificateCode,
                    'final_grade' => $enrollment->final_grade,
                    'attendance_percentage' => $eligibility['attendance_percentage'],
                    'assignment_completion_rate' => $eligibility['assignment_completion_rate'],
                    'grade_letter' => $gradeLetter,
                    'issue_date' => now(),
                    'generated_by' => $user->id,
                    'status' => 'issued',
                    'metadata' => [
                        'student_name' => $enrollment->student->user->name,
                        'course_name' => $course->course_name,
                        'instructor_name' => $course->instructor->user->name ?? null,
                    ],
                ]);

                $results['success'][] = [
                    'enrollment_id' => $enrollment->id,
                    'student_name' => $enrollment->student->user->name,
                    'certificate_id' => $certificate->id,
                    'certificate_code' => $certificate->certificate_code,
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulk certificate generation completed',
                'summary' => [
                    'total_enrollments' => $enrollments->count(),
                    'success_count' => count($results['success']),
                    'failed_count' => count($results['failed']),
                    'already_exists_count' => count($results['already_exists']),
                ],
                'results' => $results
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error bulk generating certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/certificates/verify/{certificateCode}",
     *     tags={"Certificates"},
     *     summary="Verify certificate by code",
     *     description="Public endpoint to verify certificate validity by code",
     *     @OA\Parameter(
     *         name="certificateCode",
     *         in="path",
     *         required=true,
     *         description="Certificate Code",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean"),
     *             @OA\Property(property="certificate", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Certificate not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function verify($certificateCode)
    {
        try {
            $certificate = Certificate::with(['enrollment.student.user', 'course'])
                ->where('certificate_code', $certificateCode)
                ->first();

            if (!$certificate) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Certificate not found'
                ], 404);
            }

            // Increment verification count
            $certificate->incrementVerificationCount();

            $isValid = $certificate->isValid();

            return response()->json([
                'valid' => $isValid,
                'certificate' => [
                    'certificate_code' => $certificate->certificate_code,
                    'student_name' => $certificate->enrollment->student->user->name,
                    'course_name' => $certificate->course->course_name,
                    'final_grade' => $certificate->final_grade,
                    'grade_letter' => $certificate->grade_letter,
                    'issue_date' => $certificate->issue_date,
                    'expiry_date' => $certificate->expiry_date,
                    'status' => $certificate->status,
                    'verification_count' => $certificate->verification_count,
                ],
                'message' => $isValid ? 'Certificate is valid' : 'Certificate is not valid or has been revoked/expired'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error verifying certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/certificates/verify-id/{id}",
     *     tags={"Certificates"},
     *     summary="Verify certificate by ID",
     *     description="Public endpoint to verify certificate validity by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean"),
     *             @OA\Property(property="certificate", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Certificate not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function verifyByCertificate($id)
    {
        try {
            $certificate = Certificate::with(['enrollment.student.user', 'course'])
                ->findOrFail($id);

            // Increment verification count
            $certificate->incrementVerificationCount();

            $isValid = $certificate->isValid();

            return response()->json([
                'valid' => $isValid,
                'certificate' => [
                    'id' => $certificate->id,
                    'certificate_code' => $certificate->certificate_code,
                    'student_name' => $certificate->enrollment->student->user->name,
                    'course_name' => $certificate->course->course_name,
                    'final_grade' => $certificate->final_grade,
                    'grade_letter' => $certificate->grade_letter,
                    'issue_date' => $certificate->issue_date,
                    'expiry_date' => $certificate->expiry_date,
                    'status' => $certificate->status,
                    'verification_count' => $certificate->verification_count,
                ],
                'message' => $isValid ? 'Certificate is valid' : 'Certificate is not valid or has been revoked/expired'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error verifying certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/certificates/{id}/revoke",
     *     tags={"Certificates"},
     *     summary="Revoke certificate",
     *     description="Revoke an issued certificate",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Plagiarism detected")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate revoked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Certificate revoked successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Certificate not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function revoke(Request $request, Certificate $certificate)
    {
        $this->authorize('revoke', $certificate);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $user = Auth::user();

            $certificate->revoke($validated['reason'], $user->id);
            $certificate->load(['enrollment.student.user', 'course']);

            return response()->json([
                'message' => 'Certificate revoked successfully',
                'data' => $certificate
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error revoking certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/{studentId}/certificates",
     *     tags={"Certificates"},
     *     summary="Get student certificates",
     *     description="Get all certificates for a specific student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getStudentCertificates($studentId)
    {
        try {
            $user = Auth::user();

            // Authorization: Student can view their own, instructors can view their course students
            if ($user->role === 'student' && $user->student->id != $studentId) {
                return response()->json([
                    'message' => 'You can only view your own certificates'
                ], 403);
            }

            if ($user->role === 'parent') {
                $isChild = $user->parentProfile->students()
                    ->where('students.id', $studentId)
                    ->exists();

                if (!$isChild) {
                    return response()->json([
                        'message' => 'You can only view certificates of your children'
                    ], 403);
                }
            }

            $certificates = Certificate::with(['enrollment', 'course', 'generator'])
                ->whereHas('enrollment', function($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                })
                ->orderBy('issue_date', 'desc')
                ->get();

            return response()->json($certificates);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving student certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{courseId}/certificates",
     *     tags={"Certificates"},
     *     summary="Get course certificates",
     *     description="Get all certificates issued for a specific course",
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
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getCourseCertificates($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Authorization: Instructor can only view for their courses
            if ($user->role === 'instructor' && $course->instructor_id !== $user->instructor->id) {
                return response()->json([
                    'message' => 'You can only view certificates for your courses'
                ], 403);
            }

            if ($user->role !== 'admin' && $user->role !== 'instructor') {
                return response()->json([
                    'message' => 'Only instructors and admins can view course certificates'
                ], 403);
            }

            $certificates = Certificate::with(['enrollment.student.user', 'generator'])
                ->where('course_id', $courseId)
                ->orderBy('issue_date', 'desc')
                ->get();

            return response()->json($certificates);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving course certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/certificates/check-eligibility/{enrollmentId}",
     *     tags={"Certificates"},
     *     summary="Check certificate eligibility",
     *     description="Check if a student enrollment is eligible for a certificate",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enrollmentId",
     *         in="path",
     *         required=true,
     *         description="Enrollment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Eligibility check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="eligible", type="boolean"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="details", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function checkEligibility($enrollmentId)
    {
        try {
            $user = Auth::user();
            $enrollment = Enrollment::with(['student.user', 'course'])->findOrFail($enrollmentId);

            // Authorization
            if ($user->role === 'student' && $enrollment->student_id !== $user->student->id) {
                return response()->json([
                    'message' => 'You can only check your own eligibility'
                ], 403);
            }

            if ($user->role === 'instructor') {
                $course = $enrollment->course;
                if ($course->instructor_id !== $user->instructor->id) {
                    return response()->json([
                        'message' => 'You can only check eligibility for your course students'
                    ], 403);
                }
            }

            // Check eligibility
            $eligibility = Certificate::checkEligibility($enrollment);

            return response()->json([
                'enrollment_id' => $enrollmentId,
                'student_name' => $enrollment->student->user->name,
                'course_name' => $enrollment->course->course_name,
                'eligible' => $eligibility['eligible'],
                'errors' => $eligibility['errors'],
                'details' => [
                    'final_grade' => $enrollment->final_grade,
                    'attendance_percentage' => $eligibility['attendance_percentage'],
                    'assignment_completion_rate' => $eligibility['assignment_completion_rate'],
                    'enrollment_status' => $enrollment->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking eligibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

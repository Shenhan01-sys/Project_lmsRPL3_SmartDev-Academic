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

class CertificateController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of certificates with filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Display the specified certificate and increment verification count.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
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
     * Remove the specified certificate.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
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
     * Generate certificate for an enrollment.
     *
     * @param  int  $enrollmentId
     * @return \Illuminate\Http\Response
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
     * Bulk generate certificates for all eligible students in a course.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
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
     * Verify certificate by certificate code (public endpoint).
     *
     * @param  string  $certificateCode
     * @return \Illuminate\Http\Response
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
     * Verify certificate by ID (public endpoint).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * Revoke a certificate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
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
     * Get all certificates for a student.
     *
     * @param  int  $studentId
     * @return \Illuminate\Http\Response
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
     * Get all certificates for a course.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
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
     * Check if enrollment is eligible for certificate.
     *
     * @param  int  $enrollmentId
     * @return \Illuminate\Http\Response
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

<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CertificatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view certificates list
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Certificate $certificate): bool
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can view certificates for their courses
        if ($user->role === 'instructor') {
            $course = $certificate->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        // Student can only view their own certificates
        if ($user->role === 'student') {
            $enrollment = $certificate->enrollment;
            return $enrollment && $user->student && $enrollment->student_id === $user->student->id;
        }

        // Parent can view certificates of their children
        if ($user->role === 'parent') {
            $enrollment = $certificate->enrollment;
            if (!$enrollment) {
                return false;
            }

            return $user->parentProfile && $user->parentProfile->students()
                ->where('students.id', $enrollment->student_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Certificates are generated, not manually created
        // Only admin and instructor can generate certificates
        return $user->role === 'admin' || $user->role === 'instructor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Certificate $certificate): bool
    {
        // Certificates are immutable after issuance
        // Only admin can update if absolutely necessary
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Certificate $certificate): bool
    {
        // Only admin can delete certificates
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Certificate $certificate): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Certificate $certificate): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can generate certificate for an enrollment.
     */
    public function generate(User $user, Enrollment $enrollment): bool
    {
        // Admin can generate for any enrollment
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can only generate for their course enrollments
        if ($user->role === 'instructor') {
            $course = $enrollment->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can bulk generate certificates for a course.
     */
    public function bulkGenerate(User $user, Course $course): bool
    {
        // Admin can bulk generate for any course
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can only bulk generate for their courses
        if ($user->role === 'instructor') {
            return $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can revoke a certificate.
     */
    public function revoke(User $user, Certificate $certificate): bool
    {
        // Only admin can revoke certificates
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can verify a certificate.
     * This is a public action, so everyone can verify.
     */
    public function verify(?User $user): bool
    {
        // Public endpoint - anyone can verify (even without login)
        return true;
    }

    /**
     * Determine whether the user can check eligibility for certificate.
     */
    public function checkEligibility(User $user, Enrollment $enrollment): bool
    {
        // Admin can check any enrollment
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can check for their course enrollments
        if ($user->role === 'instructor') {
            $course = $enrollment->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        // Student can check their own enrollment
        if ($user->role === 'student') {
            return $user->student && $enrollment->student_id === $user->student->id;
        }

        return false;
    }

    /**
     * Determine whether the user can download a certificate.
     */
    public function download(User $user, Certificate $certificate): bool
    {
        // Admin can download all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can download certificates for their courses
        if ($user->role === 'instructor') {
            $course = $certificate->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        // Student can only download their own certificates
        if ($user->role === 'student') {
            $enrollment = $certificate->enrollment;
            return $enrollment && $user->student && $enrollment->student_id === $user->student->id;
        }

        // Parent can download certificates of their children
        if ($user->role === 'parent') {
            $enrollment = $certificate->enrollment;
            if (!$enrollment) {
                return false;
            }

            return $user->parentProfile && $user->parentProfile->students()
                ->where('students.id', $enrollment->student_id)
                ->exists();
        }

        return false;
    }
}

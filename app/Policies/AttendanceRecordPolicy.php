<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view attendance records
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can view records for their course sessions
        if ($user->role === 'instructor') {
            $session = $attendanceRecord->attendanceSession;
            $course = $session->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        // Student can only view their own records
        if ($user->role === 'student') {
            $enrollment = $attendanceRecord->enrollment;
            return $enrollment && $user->student && $enrollment->student_id === $user->student->id;
        }

        // Parent can view records of their children
        if ($user->role === 'parent') {
            $enrollment = $attendanceRecord->enrollment;
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
        // Students can check in (create their own record)
        // Instructors can manually create records
        return in_array($user->role, ['admin', 'instructor', 'student']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Admin can update all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can update records for their course sessions
        if ($user->role === 'instructor') {
            $session = $attendanceRecord->attendanceSession;
            $course = $session->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        // Student can update their own pending records (before session closes)
        if ($user->role === 'student') {
            $enrollment = $attendanceRecord->enrollment;
            $session = $attendanceRecord->attendanceSession;

            return $enrollment
                && $user->student
                && $enrollment->student_id === $user->student->id
                && $session->status === 'open'
                && !$session->hasExpired();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Admin can delete all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can delete records for their course sessions
        if ($user->role === 'instructor') {
            $session = $attendanceRecord->attendanceSession;
            $course = $session->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can check in for attendance.
     */
    public function checkIn(User $user, AttendanceSession $attendanceSession): bool
    {
        // Only students can check in
        if ($user->role !== 'student') {
            return false;
        }

        // Session must be open and not expired
        if ($attendanceSession->status !== 'open' || $attendanceSession->hasExpired()) {
            return false;
        }

        // Student must be enrolled in the course
        return $user->student && $user->student->enrollments()
            ->where('course_id', $attendanceSession->course_id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Determine whether the user can request sick leave or permission.
     */
    public function requestLeave(User $user, AttendanceSession $attendanceSession): bool
    {
        // Only students can request leave
        if ($user->role !== 'student') {
            return false;
        }

        // Student must be enrolled in the course
        return $user->student && $user->student->enrollments()
            ->where('course_id', $attendanceSession->course_id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Determine whether the user can review attendance records (approve/reject).
     */
    public function review(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Admin can review all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can review records for their course sessions
        if ($user->role === 'instructor') {
            $session = $attendanceRecord->attendanceSession;
            $course = $session->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can bulk mark attendance.
     */
    public function bulkMark(User $user, AttendanceSession $attendanceSession): bool
    {
        // Admin can bulk mark for any session
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can bulk mark for their course sessions
        if ($user->role === 'instructor') {
            $course = $attendanceSession->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }
}

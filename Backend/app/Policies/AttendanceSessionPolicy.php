<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view attendance sessions list
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttendanceSession $attendanceSession): bool
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can view sessions for their courses
        if ($user->role === 'instructor') {
            $course = $attendanceSession->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        // Student can view sessions for courses they are enrolled in
        if ($user->role === 'student') {
            return $user->student && Enrollment::where('student_id', $user->student->id)
                ->where('course_id', $attendanceSession->course_id)
                ->where('status', 'active')
                ->exists();
        }

        // Parent can view sessions for courses their children are enrolled in
        if ($user->role === 'parent') {
            return $user->parentProfile && $user->parentProfile->students()->whereHas('enrollments', function($query) use ($attendanceSession) {
                $query->where('course_id', $attendanceSession->course_id)
                    ->where('status', 'active');
            })->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin and instructor can create attendance sessions
        return $user->role === 'admin' || $user->role === 'instructor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttendanceSession $attendanceSession): bool
    {
        // Admin can update all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can only update sessions for their courses
        if ($user->role === 'instructor') {
            $course = $attendanceSession->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttendanceSession $attendanceSession): bool
    {
        // Admin can delete all
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can only delete sessions for their courses
        if ($user->role === 'instructor') {
            $course = $attendanceSession->course;
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create attendance session for a specific course.
     */
    public function createForCourse(User $user, Course $course): bool
    {
        // Admin can create for any course
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor can only create for their courses
        if ($user->role === 'instructor') {
            return $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can open/close the session.
     */
    public function manageStatus(User $user, AttendanceSession $attendanceSession): bool
    {
        // Same as update permission
        return $this->update($user, $attendanceSession);
    }
}

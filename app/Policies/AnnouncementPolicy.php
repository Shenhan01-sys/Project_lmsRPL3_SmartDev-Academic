<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua authenticated user bisa lihat list announcements
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        // Admin bisa lihat semua
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor bisa lihat semua announcement
        if ($user->role === 'instructor') {
            return true;
        }

        // Student hanya bisa lihat published announcement
        if ($user->role === 'student') {
            // Harus published
            if (!$announcement->isPublished()) {
                return false;
            }

            // Global announcement bisa dilihat semua student
            if ($announcement->isGlobal()) {
                return true;
            }

            // Course announcement: cek apakah student enrolled
            if ($announcement->course_id) {
                return $user->student && Enrollment::where('student_id', $user->student->id)
                    ->where('course_id', $announcement->course_id)
                    ->exists();
            }

            return false;
        }

        // Parent bisa lihat announcement dari course yang diikuti anak mereka
        if ($user->role === 'parent') {
            // Harus published
            if (!$announcement->isPublished()) {
                return false;
            }

            // Global announcement bisa dilihat
            if ($announcement->isGlobal()) {
                return true;
            }

            // Course announcement: cek apakah anak mereka enrolled
            if ($announcement->course_id) {
                return $user->parentProfile && $user->parentProfile->students()->whereHas('enrollments', function($query) use ($announcement) {
                    $query->where('course_id', $announcement->course_id);
                })->exists();
            }

            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'instructor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        // Admin bisa update semua
        if ($user->role === 'admin') {
            return true;
        }

        // Creator bisa update announcement mereka
        if ($announcement->created_by === $user->id) {
            return true;
        }

        // Instructor bisa update announcement untuk course mereka
        if ($user->role === 'instructor' && $announcement->course_id) {
            $course = Course::find($announcement->course_id);
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        // Admin bisa delete semua
        if ($user->role === 'admin') {
            return true;
        }

        // Creator bisa delete announcement mereka
        if ($announcement->created_by === $user->id) {
            return true;
        }

        // Instructor bisa delete announcement untuk course mereka
        if ($user->role === 'instructor' && $announcement->course_id) {
            $course = Course::find($announcement->course_id);
            return $course && $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can publish the announcement.
     */
    public function publish(User $user, Announcement $announcement): bool
    {
        // Same as update
        return $this->update($user, $announcement);
    }

    /**
     * Determine whether the user can archive the announcement.
     */
    public function archive(User $user, Announcement $announcement): bool
    {
        // Same as update
        return $this->update($user, $announcement);
    }

    /**
     * Determine whether the user can create global announcements.
     */
    public function createGlobal(User $user): bool
    {
        // Hanya admin yang bisa buat global announcement
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create announcements for a specific course.
     */
    public function createForCourse(User $user, Course $course): bool
    {
        // Admin bisa create untuk semua course
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor hanya bisa create untuk course mereka
        if ($user->role === 'instructor') {
            return $user->instructor && $course->instructor_id === $user->instructor->id;
        }

        return false;
    }
}

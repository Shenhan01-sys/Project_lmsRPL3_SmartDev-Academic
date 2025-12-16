<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'instructor';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        // Instructor can view submissions in their course
        if ($user->role === 'instructor' && $user->instructor) {
            // Pastikan akses ke course melalui assignment
            return $user->instructor->id === $submission->assignment->course->instructor_id;
        }
        
        // Student can view their own submission
        if ($user->role === 'student' && $user->student) {
            // Cek kepemilikan via enrollment
            return $submission->enrollment && $submission->enrollment->student_id === $user->student->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Submission $submission): bool
    {
        // 1. Admin boleh update apa saja
        if ($user->role === 'admin') {
            return true;
        }

        // 2. Instructor boleh update (untuk memberi NILAI & FEEDBACK)
        if ($user->role === 'instructor' && $user->instructor) {
            // Cek apakah instruktur ini yang mengajar course dari tugas tersebut
            return $user->instructor->id === $submission->assignment->course->instructor_id;
        }

        // 3. Student boleh update (untuk RE-UPLOAD tugas, jika belum dinilai/deadline belum lewat)
        if ($user->role === 'student' && $user->student) {
            // Cek kepemilikan via enrollment
            return $submission->enrollment && $submission->enrollment->student_id === $user->student->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Submission $submission): bool
    {
        // Admin boleh hapus
        if ($user->role === 'admin') {
            return true;
        }

        // Student boleh hapus submission sendiri
        if ($user->role === 'student' && $user->student) {
            return $submission->enrollment && $submission->enrollment->student_id === $user->student->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Submission $submission): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Submission $submission): bool
    {
        return $user->role === 'admin';
    }
}
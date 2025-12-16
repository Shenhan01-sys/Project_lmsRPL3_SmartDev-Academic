<?php

namespace App\Policies;

use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentRegistrationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view any registrations.
     * Hanya admin yang bisa melihat semua registrasi.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can view the registration.
     * Admin bisa lihat semua, calon_siswa hanya bisa lihat miliknya sendiri.
     */
    public function view(User $user, StudentRegistration $registration): bool
    {
        // Admin bisa lihat semua
        if ($user->role === 'admin') {
            return true;
        }

        // Calon siswa hanya bisa lihat registrasi miliknya sendiri
        return $user->id === $registration->user_id && $user->role === 'calon_siswa';
    }

    /**
     * Determine if the user can create registrations. 
     * Semua user yang belum register bisa buat registrasi (public endpoint).
     */
    public function create(? User $user): bool
    {
        // Return true karena endpoint register bersifat public
        return true;
    }

    /**
     * Determine if the user can update the registration.
     * Hanya admin yang bisa approve/reject. 
     */
    public function update(User $user, StudentRegistration $registration): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can delete the registration.
     * Hanya admin yang bisa delete (opsional, bisa disesuaikan).
     */
    public function delete(User $user, StudentRegistration $registration): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can upload documents.
     * Hanya calon_siswa yang bisa upload dokumen untuk registrasinya sendiri.
     */
    public function uploadDocuments(User $user, StudentRegistration $registration): bool
    {
        return $user->id === $registration->user_id && $user->role === 'calon_siswa';
    }

    /**
     * Determine if the user can check their own registration status.
     * Hanya calon_siswa yang bisa cek status registrasinya sendiri.
     */
    public function checkStatus(User $user): bool
    {
        return $user->role === 'calon_siswa';
    }
}
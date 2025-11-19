<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their own notifications
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Notification $notification): bool
    {
        // User can only view their own notifications
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Notifications are created by the system, not manually by users
        // Only allow admin to manually create if needed
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Notification $notification): bool
    {
        // User can only update (mark as read/unread) their own notifications
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Notification $notification): bool
    {
        // User can only delete their own notifications
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Notification $notification): bool
    {
        // Only admin can restore deleted notifications
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Notification $notification): bool
    {
        // Only admin can force delete notifications
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can mark notifications as read.
     */
    public function markAsRead(User $user, Notification $notification): bool
    {
        // Same as update - user can only mark their own notifications
        return $this->update($user, $notification);
    }

    /**
     * Determine whether the user can mark notifications as unread.
     */
    public function markAsUnread(User $user, Notification $notification): bool
    {
        // Same as update - user can only mark their own notifications
        return $this->update($user, $notification);
    }

    /**
     * Determine whether the user can bulk mark notifications as read.
     */
    public function bulkMarkAsRead(User $user): bool
    {
        // All users can bulk mark their own notifications as read
        return true;
    }

    /**
     * Determine whether the user can bulk delete notifications.
     */
    public function bulkDelete(User $user): bool
    {
        // All users can bulk delete their own notifications
        return true;
    }

    /**
     * Determine whether the user can delete all read notifications.
     */
    public function deleteAllRead(User $user): bool
    {
        // All users can delete all their read notifications
        return true;
    }
}

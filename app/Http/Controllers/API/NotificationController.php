<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     tags={"Notifications"},
     *     summary="Get user notifications",
     *     description="Retrieve all notifications for authenticated user with filtering options",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="notification_type",
     *         in="query",
     *         description="Filter by notification type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Filter by read status",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         @OA\Schema(type="string", enum={"low", "normal", "high"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Notification::class);

        try {
            $user = Auth::user();
            $query = Notification::where("user_id", $user->id);

            // Filtering
            if ($request->has("notification_type")) {
                $query->ofType($request->notification_type);
            }

            if ($request->has("is_read")) {
                $isRead = filter_var(
                    $request->is_read,
                    FILTER_VALIDATE_BOOLEAN,
                );
                if ($isRead) {
                    $query->read();
                } else {
                    $query->unread();
                }
            }

            if ($request->has("priority")) {
                $query->where("priority", $request->priority);
            }

            // Only active (non-expired) notifications
            $query->active();

            // Sorting
            $notifications = $query
                ->orderBy("created_at", "desc")
                ->paginate(20);

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving notifications",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/{id}",
     *     tags={"Notifications"},
     *     summary="Get notification by ID",
     *     description="Retrieve a specific notification and automatically mark as read",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification retrieved successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Notification not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Notification $notification)
    {
        $this->authorize("view", $notification);

        try {
            // Auto mark as read when opened
            if (!$notification->is_read) {
                $notification->markAsRead();
            }

            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving notification",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notifications/{id}",
     *     tags={"Notifications"},
     *     summary="Delete notification",
     *     description="Remove a specific notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Notification not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Notification $notification)
    {
        $this->authorize("delete", $notification);

        try {
            $notification->delete();

            return response()->json(
                [
                    "message" => "Notification deleted successfully",
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting notification",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/{id}/mark-read",
     *     tags={"Notifications"},
     *     summary="Mark notification as read",
     *     description="Mark a specific notification as read",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification marked as read"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Notification not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function markAsRead(Notification $notification)
    {
        $this->authorize("update", $notification);

        try {
            $notification->markAsRead();

            return response()->json([
                "message" => "Notification marked as read",
                "data" => $notification,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error marking notification as read",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/{id}/mark-unread",
     *     tags={"Notifications"},
     *     summary="Mark notification as unread",
     *     description="Mark a specific notification as unread",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as unread",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification marked as unread"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Notification not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function markAsUnread(Notification $notification)
    {
        $this->authorize("update", $notification);

        try {
            $notification->markAsUnread();

            return response()->json([
                "message" => "Notification marked as unread",
                "data" => $notification,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error marking notification as unread",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/mark-all-read",
     *     tags={"Notifications"},
     *     summary="Mark all notifications as read",
     *     description="Mark all unread notifications for authenticated user as read",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="All notifications marked as read"),
     *             @OA\Property(property="count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function markAllAsRead()
    {
        $this->authorize("viewAny", Notification::class);

        try {
            $user = Auth::user();

            $updated = Notification::where("user_id", $user->id)
                ->unread()
                ->update([
                    "is_read" => true,
                    "read_at" => now(),
                ]);

            return response()->json([
                "message" => "All notifications marked as read",
                "count" => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error marking all notifications as read",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/unread",
     *     tags={"Notifications"},
     *     summary="Get unread notifications",
     *     description="Retrieve all unread notifications for authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unread notifications retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getUnreadNotifications()
    {
        $this->authorize("viewAny", Notification::class);

        try {
            $user = Auth::user();

            $notifications = Notification::where("user_id", $user->id)
                ->unread()
                ->active()
                ->orderBy("created_at", "desc")
                ->get();

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving unread notifications",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get read notifications for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getReadNotifications()
    {
        $this->authorize("viewAny", Notification::class);

        try {
            $user = Auth::user();

            $notifications = Notification::where("user_id", $user->id)
                ->read()
                ->active()
                ->orderBy("created_at", "desc")
                ->paginate(20);

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving read notifications",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get notifications by type.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function getByType($type)
    {
        $this->authorize("viewAny", Notification::class);

        try {
            $user = Auth::user();

            $notifications = Notification::where("user_id", $user->id)
                ->ofType($type)
                ->active()
                ->orderBy("created_at", "desc")
                ->paginate(20);

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving notifications by type",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/unread-count",
     *     tags={"Notifications"},
     *     summary="Get unread notification count",
     *     description="Get the count of unread notifications for authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unread count retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="unread_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getUnreadCount()
    {
        $this->authorize("viewAny", Notification::class);

        try {
            $user = Auth::user();

            $count = Notification::where("user_id", $user->id)
                ->unread()
                ->active()
                ->count();

            return response()->json([
                "unread_count" => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving unread count",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/bulk-mark-read",
     *     tags={"Notifications"},
     *     summary="Bulk mark notifications as read",
     *     description="Mark multiple notifications as read at once",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notification_ids"},
     *             @OA\Property(
     *                 property="notification_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notifications marked as read"),
     *             @OA\Property(property="count", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function bulkMarkAsRead(Request $request)
    {
        $this->authorize("bulkMarkAsRead", Notification::class);

        $validated = $request->validate([
            "notification_ids" => "required|array",
            "notification_ids.*" => "required|integer|exists:notifications,id",
        ]);

        try {
            $user = Auth::user();

            $updated = Notification::where("user_id", $user->id)
                ->whereIn("id", $validated["notification_ids"])
                ->unread()
                ->update([
                    "is_read" => true,
                    "read_at" => now(),
                ]);

            return response()->json([
                "message" => "Notifications marked as read",
                "count" => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error marking notifications as read",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/bulk-delete",
     *     tags={"Notifications"},
     *     summary="Bulk delete notifications",
     *     description="Delete multiple notifications at once",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notification_ids"},
     *             @OA\Property(
     *                 property="notification_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notifications deleted successfully"),
     *             @OA\Property(property="count", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function bulkDelete(Request $request)
    {
        $this->authorize("bulkDelete", Notification::class);

        $validated = $request->validate([
            "notification_ids" => "required|array",
            "notification_ids.*" => "required|integer|exists:notifications,id",
        ]);

        try {
            $user = Auth::user();

            $deleted = Notification::where("user_id", $user->id)
                ->whereIn("id", $validated["notification_ids"])
                ->delete();

            return response()->json([
                "message" => "Notifications deleted successfully",
                "count" => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting notifications",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Delete all read notifications for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAllRead()
    {
        $this->authorize("deleteAllRead", Notification::class);

        try {
            $user = Auth::user();

            $deleted = Notification::where("user_id", $user->id)
                ->read()
                ->delete();

            return response()->json([
                "message" => "All read notifications deleted successfully",
                "count" => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting read notifications",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

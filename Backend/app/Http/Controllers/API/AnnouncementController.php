<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/announcements",
     *     tags={"Announcements"},
     *     summary="Get all announcements",
     *     description="Retrieve announcements with filtering options (students see only published announcements for enrolled courses)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filter by course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="announcement_type",
     *         in="query",
     *         description="Filter by announcement type",
     *         @OA\Schema(type="string", enum={"global", "course"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"draft", "published", "archived"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcements retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Announcement::class);

        try {
            $user = Auth::user();
            $query = Announcement::with(["creator", "course"]);

            // Filtering
            if ($request->has("course_id")) {
                $query->where("course_id", $request->course_id);
            }

            if ($request->has("announcement_type")) {
                $query->where("announcement_type", $request->announcement_type);
            }

            if ($request->has("status")) {
                $query->where("status", $request->status);
            }

            // Authorization: Students only see published & active announcements for their courses
            if ($user->role === "student") {
                $enrolledCourseIds = Enrollment::where(
                    "student_id",
                    $user->student->id,
                )->pluck("course_id");

                $query
                    ->where(function ($q) use ($enrolledCourseIds) {
                        $q->where("announcement_type", "global")->orWhereIn(
                            "course_id",
                            $enrolledCourseIds,
                        );
                    })
                    ->published()
                    ->active();
            }

            // Sorting
            $announcements = $query
                ->orderBy("published_at", "desc")
                ->paginate(15);

            return response()->json($announcements);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving announcements",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/announcements",
     *     tags={"Announcements"},
     *     summary="Create new announcement",
     *     description="Create a new announcement (global or course-specific)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "announcement_type"},
     *             @OA\Property(property="title", type="string", example="Important Update"),
     *             @OA\Property(property="content", type="string", example="This is an important announcement..."),
     *             @OA\Property(property="announcement_type", type="string", enum={"global", "course"}, example="course"),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}, example="draft"),
     *             @OA\Property(property="published_at", type="string", format="date-time"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize("create", Announcement::class);

        $user = Auth::user();

        $validated = $request->validate([
            "title" => "required|string|max:255",
            "content" => "required|string",
            "announcement_type" => "required|in:global,course",
            "course_id" =>
                "required_if:announcement_type,course|nullable|exists:courses,id",
            "status" => "nullable|in:draft,published,archived",
            "published_at" => "nullable|date",
            "expires_at" => "nullable|date|after:published_at",
        ]);

        try {
            // Authorization: Check if user can create global announcement
            if ($validated["announcement_type"] === "global") {
                $this->authorize("createGlobal", Announcement::class);
            }

            // Authorization: Check if user can create announcement for this course
            if ($validated["announcement_type"] === "course") {
                $course = Course::findOrFail($validated["course_id"]);
                $this->authorize("createForCourse", [
                    Announcement::class,
                    $course,
                ]);
            }

            // Set created_by
            $validated["created_by"] = $user->id;

            // Set default status if not provided
            if (!isset($validated["status"])) {
                $validated["status"] = "draft";
            }

            $announcement = Announcement::create($validated);
            $announcement->load(["creator", "course"]);

            return response()->json($announcement, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating announcement",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/announcements/{id}",
     *     tags={"Announcements"},
     *     summary="Get announcement by ID",
     *     description="Retrieve a specific announcement and increment view count",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Announcement ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement retrieved successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Announcement not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Announcement $announcement)
    {
        $this->authorize("view", $announcement);

        try {
            // Increment view count
            $announcement->incrementViewCount();

            // Load relationships
            $announcement->load(["creator.user", "course"]);

            return response()->json($announcement);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving announcement",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/announcements/{id}",
     *     tags={"Announcements"},
     *     summary="Update announcement",
     *     description="Update an existing announcement",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Announcement ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "announcement_type"},
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="content", type="string", example="Updated content..."),
     *             @OA\Property(property="announcement_type", type="string", enum={"global", "course"}),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}),
     *             @OA\Property(property="published_at", type="string", format="date-time"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Announcement not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize("update", $announcement);

        $validated = $request->validate([
            "title" => "required|string|max:255",
            "content" => "required|string",
            "announcement_type" => "required|in:global,course",
            "course_id" =>
                "required_if:announcement_type,course|nullable|exists:courses,id",
            "status" => "nullable|in:draft,published,archived",
            "published_at" => "nullable|date",
            "expires_at" => "nullable|date|after:published_at",
        ]);

        try {
            // Authorization: Check if user can create global announcement
            if ($validated["announcement_type"] === "global") {
                $this->authorize("createGlobal", Announcement::class);
            }

            // Authorization: Check if user can create announcement for this course
            if ($validated["announcement_type"] === "course") {
                $course = Course::findOrFail($validated["course_id"]);
                $this->authorize("createForCourse", [
                    Announcement::class,
                    $course,
                ]);
            }

            $announcement->update($validated);
            $announcement->load(["creator.user", "course"]);

            return response()->json($announcement);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating announcement",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/announcements/{id}",
     *     tags={"Announcements"},
     *     summary="Delete announcement",
     *     description="Remove an announcement",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Announcement ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Announcement deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Announcement not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Announcement $announcement)
    {
        $this->authorize("delete", $announcement);

        try {
            $announcement->delete();

            return response()->json(
                [
                    "message" => "Announcement deleted successfully",
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting announcement",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/announcements/{id}/publish",
     *     tags={"Announcements"},
     *     summary="Publish announcement",
     *     description="Change announcement status to published",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Announcement ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement published successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Announcement published successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Announcement not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function publish(Announcement $announcement)
    {
        $this->authorize("publish", $announcement);

        try {
            $announcement->publish();
            $announcement->load(["creator.user", "course"]);

            return response()->json([
                "message" => "Announcement published successfully",
                "data" => $announcement,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error publishing announcement",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/announcements/{id}/archive",
     *     tags={"Announcements"},
     *     summary="Archive announcement",
     *     description="Change announcement status to archived",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Announcement ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement archived successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Announcement archived successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Announcement not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function archive(Announcement $announcement)
    {
        $this->authorize("archive", $announcement);

        try {
            $announcement->archive();
            $announcement->load(["creator.user", "course"]);

            return response()->json([
                "message" => "Announcement archived successfully",
                "data" => $announcement,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error archiving announcement",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/announcements/course/{courseId}",
     *     tags={"Announcements"},
     *     summary="Get course announcements",
     *     description="Retrieve all announcements for a specific course (including global announcements)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course announcements retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized or not enrolled"),
     *     @OA\Response(response=404, description="Course not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getCourseAnnouncements($courseId)
    {
        try {
            $user = Auth::user();

            // Check if course exists
            $course = Course::find($courseId);
            if (!$course) {
                return response()->json(
                    [
                        "message" => "Course not found",
                    ],
                    404,
                );
            }

            // Authorization: Check if user has access to this course
            if ($user->role === "student") {
                $isEnrolled = Enrollment::where(
                    "student_id",
                    $user->student->id,
                )
                    ->where("course_id", $courseId)
                    ->exists();

                if (!$isEnrolled) {
                    return response()->json(
                        [
                            "message" => "You are not enrolled in this course",
                        ],
                        403,
                    );
                }
            } elseif ($user->role === "instructor") {
                if ($course->instructor_id !== $user->instructor->id) {
                    return response()->json(
                        [
                            "message" =>
                                "You are not the instructor of this course",
                        ],
                        403,
                    );
                }
            }

            // Get global announcements + course-specific announcements
            $announcements = Announcement::with(["creator", "course"])
                ->forCourse($courseId)
                ->published()
                ->active()
                ->orderBy("priority", "desc")
                ->orderBy("published_at", "desc")
                ->get();

            return response()->json($announcements);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving course announcements",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/announcements/global/list",
     *     tags={"Announcements"},
     *     summary="Get global announcements",
     *     description="Retrieve all published global announcements",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Global announcements retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getGlobalAnnouncements()
    {
        try {
            $announcements = Announcement::with(["creator.user"])
                ->global()
                ->published()
                ->active()
                ->orderBy("priority", "desc")
                ->orderBy("published_at", "desc")
                ->get();

            return response()->json($announcements);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving global announcements",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/announcements/active/list",
     *     tags={"Announcements"},
     *     summary="Get active announcements",
     *     description="Retrieve all active (non-expired) announcements for the user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active announcements retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getActiveAnnouncements()
    {
        try {
            $user = Auth::user();
            $query = Announcement::with(["creator.user", "course"])->active();

            // Students only see announcements for their courses + global
            if ($user->role === "student") {
                $enrolledCourseIds = Enrollment::where(
                    "student_id",
                    $user->student->id,
                )->pluck("course_id");

                $query->where(function ($q) use ($enrolledCourseIds) {
                    $q->where("announcement_type", "global")->orWhereIn(
                        "course_id",
                        $enrolledCourseIds,
                    );
                });
            }

            $announcements = $query
                ->orderBy("priority", "desc")
                ->orderBy("published_at", "desc")
                ->get();

            return response()->json($announcements);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving active announcements",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

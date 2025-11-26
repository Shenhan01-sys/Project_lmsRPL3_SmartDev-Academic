<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseModuleController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/course-modules",
     *     tags={"Course Modules"},
     *     summary="Get all course modules",
     *     description="Retrieve course modules based on user role (students see only modules from enrolled courses)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Course modules retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="module_name", type="string", example="Introduction to Programming"),
     *                 @OA\Property(property="module_order", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="Basic programming concepts")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index()
    {
        try {
            // Authorization: Check if user can view any course modules
            $this->authorize("viewAny", CourseModule::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin bisa lihat semua course modules
                $modules = CourseModule::with("course")->get();
            } elseif ($user->role === "instructor") {
                // Instructor hanya bisa lihat modules dari course yang dia ajar
                // Get instructor record from user
                $instructor = $user->instructor;
                
                if (!$instructor) {
                    return response()->json([
                        "message" => "Instructor record not found for this user",
                    ], 404);
                }
                
                $modules = CourseModule::with("course")
                    ->whereHas("course", function ($query) use ($instructor) {
                        $query->where("instructor_id", $instructor->id);
                    })
                    ->get();
            } elseif ($user->role === "student") {
                // Student hanya bisa lihat modules dari course yang dia ikuti
                $modules = CourseModule::with("course")
                    ->whereHas("course.enrollments", function ($query) use (
                        $user,
                    ) {
                        $query->where("student_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "parent") {
                // Parent bisa lihat modules dari course yang anaknya ikuti
                $childrenIds = \App\Models\User::where(
                    "parent_id",
                    $user->id,
                )->pluck("id");
                $modules = CourseModule::with("course")
                    ->whereHas("course.enrollments", function ($query) use (
                        $childrenIds,
                    ) {
                        $query->whereIn("student_id", $childrenIds);
                    })
                    ->get();
            } else {
                $modules = collect();
            }

            return response()->json($modules);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving course modules",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/course-modules",
     *     tags={"Course Modules"},
     *     summary="Create new course module",
     *     description="Create a new module for a course",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id", "module_name", "module_order"},
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="module_name", type="string", example="Week 1: Introduction"),
     *             @OA\Property(property="module_order", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Introduction to the course")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course module created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="module_name", type="string", example="Week 1: Introduction"),
     *             @OA\Property(property="module_order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        // Authorization: Check if user can create course modules
        $this->authorize("create", CourseModule::class);

        $validated = $request->validate([
            "course_id" => "required|exists:courses,id",
            "title" => "required|string|max:255",
            "module_order" => "nullable|integer",
        ]);

        try {
            $module = CourseModule::create($validated);
            return response()->json($module, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating course module",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/course-modules/{id}",
     *     tags={"Course Modules"},
     *     summary="Get course module by ID",
     *     description="Retrieve a specific course module with its materials",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course Module ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="module_name", type="string", example="Week 1: Introduction"),
     *             @OA\Property(property="module_order", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Introduction to the course")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Course module not found")
     * )
     */
    public function show(CourseModule $courseModule)
    {
        // Authorization: Check if user can view this course module
        $this->authorize("view", $courseModule);

        $courseModule->load("course", "materials");
        return response()->json($courseModule);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/course-modules/{id}",
     *     tags={"Course Modules"},
     *     summary="Update course module",
     *     description="Update an existing course module",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course Module ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="module_name", type="string", example="Week 1: Updated Introduction"),
     *             @OA\Property(property="module_order", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course module updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Course module not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, CourseModule $courseModule)
    {
        // Authorization: Check if user can update this course module
        $this->authorize("update", $courseModule);

        $validated = $request->validate([
            "course_id" => "sometimes|required|exists:courses,id",
            "title" => "sometimes|required|string|max:255",
            "module_order" => "nullable|integer",
        ]);

        try {
            $courseModule->update($validated);
            return response()->json($courseModule);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating course module",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/course-modules/{id}",
     *     tags={"Course Modules"},
     *     summary="Delete course module",
     *     description="Remove a course module",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course Module ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Course module deleted successfully"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Course module not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(CourseModule $courseModule)
    {
        // Authorization: Check if user can delete this course module
        $this->authorize("delete", $courseModule);

        try {
            $courseModule->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting course module",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get all course modules with preview mode for discovery
     * Students can see all modules but only access enrolled ones
     */
    public function browse()
    {
        try {
            $this->authorize("viewAny", CourseModule::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin bisa lihat semua dengan full access
                $modules = CourseModule::with("course", "materials")->get();
                return $modules->map(function ($module) {
                    $module->can_access = true;
                    $module->access_level = "full";
                    return $module;
                });
            } elseif ($user->role === "instructor") {
                // Instructor lihat semua, tapi hanya full access ke course sendiri
                $modules = CourseModule::with("course")->get();
                return $modules->map(function ($module) use ($user) {
                    $canAccess = $module->course->instructor_id === $user->id;
                    $module->can_access = $canAccess;
                    $module->access_level = $canAccess ? "full" : "preview";

                    if (!$canAccess) {
                        $module->makeHidden(["materials"]);
                    } else {
                        $module->load("materials");
                    }

                    return $module;
                });
            } elseif (in_array($user->role, ["student", "parent"])) {
                // Student/Parent bisa lihat semua modules untuk discovery
                $modules = CourseModule::with("course")->get();

                // Get enrolled course IDs
                $enrolledCourseIds = collect();
                if ($user->role === "student") {
                    $enrolledCourseIds = Enrollment::where(
                        "student_id",
                        $user->id,
                    )->pluck("course_id");
                } else {
                    // parent
                    $childrenIds = \App\Models\User::where(
                        "parent_id",
                        $user->id,
                    )->pluck("id");
                    $enrolledCourseIds = Enrollment::whereIn(
                        "student_id",
                        $childrenIds,
                    )
                        ->pluck("course_id")
                        ->unique();
                }

                return $modules->map(function ($module) use (
                    $enrolledCourseIds,
                ) {
                    $canAccess = $enrolledCourseIds->contains(
                        $module->course_id,
                    );
                    $module->can_access = $canAccess;
                    $module->access_level = $canAccess ? "full" : "preview";

                    if ($canAccess) {
                        $module->load("materials");
                    } else {
                        // Preview mode: show basic info only
                        $module->description =
                            "Enroll in this course to access module content";
                        $module->makeHidden(["materials"]);
                    }

                    return $module;
                });
            }

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error browsing course modules",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get only enrolled course modules (full access)
     */
    public function myModules()
    {
        try {
            $this->authorize("viewAny", CourseModule::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin bisa lihat semua
                $modules = CourseModule::with("course", "materials")->get();
            } elseif ($user->role === "instructor") {
                // Instructor hanya modules dari course yang dia ajar
                $modules = CourseModule::with("course", "materials")
                    ->whereHas("course", function ($query) use ($user) {
                        $query->where("instructor_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "student") {
                // Student hanya modules dari course yang dia ikuti
                $modules = CourseModule::with("course", "materials")
                    ->whereHas("course.enrollments", function ($query) use (
                        $user,
                    ) {
                        $query->where("student_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "parent") {
                // Parent modules dari course yang anaknya ikuti
                $childrenIds = \App\Models\User::where(
                    "parent_id",
                    $user->id,
                )->pluck("id");
                $modules = CourseModule::with("course", "materials")
                    ->whereHas("course.enrollments", function ($query) use (
                        $childrenIds,
                    ) {
                        $query->whereIn("student_id", $childrenIds);
                    })
                    ->get();
            } else {
                $modules = collect();
            }

            // Add full access indicator
            $modules = $modules->map(function ($module) {
                $module->can_access = true;
                $module->access_level = "full";
                return $module;
            });

            return response()->json($modules);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving my course modules",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

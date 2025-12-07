<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/materials",
     *     tags={"Materials"},
     *     summary="Get all materials",
     *     description="Retrieve materials based on user role (students see only materials from enrolled courses)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Materials retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="course_module_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Introduction to Programming"),
     *                 @OA\Property(property="content_type", type="string", example="document"),
     *                 @OA\Property(property="content_path", type="string", example="/materials/intro.pdf"),
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
            // Authorization: Check if user can view any materials
            $this->authorize("viewAny", Material::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin bisa lihat semua materials
                $materials = Material::with("courseModule.course")->get();
            } elseif ($user->role === "instructor") {
                // Instructor hanya bisa lihat materials dari course yang dia ajar
                // Get instructor record from user
                $instructor = $user->instructor;
                
                if (!$instructor) {
                    return response()->json([
                        "message" => "Instructor record not found for this user",
                    ], 404);
                }
                
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course", function ($query) use ($instructor) {
                        $query->where("instructor_id", $instructor->id);
                    })
                    ->get();
            } elseif ($user->role === "student") {
                // Student hanya bisa lihat materials dari course yang dia ikuti
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function (
                        $query,
                    ) use ($user) {
                        $query->where("student_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "parent") {
                // Parent bisa lihat materials dari course yang anaknya ikuti
                $childrenIds = \App\Models\User::where(
                    "parent_id",
                    $user->id,
                )->pluck("id");
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function (
                        $query,
                    ) use ($childrenIds) {
                        $query->whereIn("student_id", $childrenIds);
                    })
                    ->get();
            } else {
                $materials = collect();
            }

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving materials",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
     {
         $this->authorize("create", Material::class);
     
         // 1. Validasi Input
         // Kita ubah validasi agar fleksibel (file atau string)
         $request->validate([
             "module_id" => "required|exists:course_modules,id",
             "title" => "required|string|max:255",
             "material_type" => "required|in:file,link,video",
             // Hapus validasi 'content_path' string disini, kita handle manual di bawah
         ]);
     
         try {
             $path = null;
     
             // 2. Cek Tipe Materi
             if ($request->material_type === 'file') {
                 // Validasi khusus file
                 $request->validate([
                     'content_file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,jpg,png,mp4|max:51200' // Max 50MB
                 ]);
     
                 // 3. PROSES UPLOAD FILE KE SERVER
                 if ($request->hasFile('content_file')) {
                     // Simpan ke folder: storage/app/public/materials
                     $path = $request->file('content_file')->store('materials', 'public');
                 }
             } else {
                 // Jika Link/Video
                 $request->validate([
                     'content_url' => 'required|string'
                 ]);
                 $path = $request->content_url;
             }
     
             // 4. Simpan ke Database
             $material = Material::create([
                 'course_module_id' => $request->module_id, // Perhatikan nama kolom di DB mu (module_id atau course_module_id?)
                 'title' => $request->title,
                 'material_type' => $request->material_type,
                 'content_path' => $path, // Path hasil upload atau URL link
                 'description' => $request->description ?? null
             ]);
     
             return response()->json($material, 201);
     
         } catch (\Exception $e) {
             return response()->json([
                 "message" => "Error creating material",
                 "error" => $e->getMessage(),
             ], 500);
         }
     }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $material)
    {
        // Authorization: Check if user can view this material
        $this->authorize("view", $material);

        $material->load("courseModule");
        return response()->json($material);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Material $material)
    {
        // Authorization: Check if user can update this material
        $this->authorize("update", $material);

        $validated = $request->validate([
            "module_id" => "sometimes|required|exists:course_modules,id",
            "title" => "sometimes|required|string|max:255",
            "material_type" => "sometimes|required|in:file,link,video",
            "content_path" => "sometimes|required|string",
        ]);

        try {
            $material->update($validated);
            return response()->json($material);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating material",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function destroy(Material $material)
    {
        // Authorization: Check if user can delete this material
        $this->authorize("delete", $material);

        try {
            $material->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting material",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Browse all materials with preview mode for discovery
     */
    public function browse()
    {
        try {
            $this->authorize("viewAny", Material::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin full access to all materials
                $materials = Material::with("courseModule.course")->get();
                return $materials->map(function ($material) {
                    $material->can_access = true;
                    $material->access_level = "full";
                    return $material;
                });
            } elseif ($user->role === "instructor") {
                // Instructor can see all, but full access only to own courses
                $materials = Material::with("courseModule.course")->get();
                return $materials->map(function ($material) use ($user) {
                    $canAccess =
                        $material->courseModule->course->instructor_id ===
                        $user->id;
                    $material->can_access = $canAccess;
                    $material->access_level = $canAccess ? "full" : "preview";

                    if (!$canAccess) {
                        // Hide actual content path in preview mode
                        $material->content_path =
                            "Preview available after course enrollment";
                    }

                    return $material;
                });
            } elseif (in_array($user->role, ["student", "parent"])) {
                // Student/Parent can browse all materials
                $materials = Material::with("courseModule.course")->get();

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

                return $materials->map(function ($material) use (
                    $enrolledCourseIds,
                ) {
                    $canAccess = $enrolledCourseIds->contains(
                        $material->courseModule->course_id,
                    );
                    $material->can_access = $canAccess;
                    $material->access_level = $canAccess ? "full" : "preview";

                    if (!$canAccess) {
                        // Preview mode: hide actual content
                        $material->content_path =
                            "Enroll in course to access this material";
                        $material->description =
                            "Preview: " .
                            $material->title .
                            " - Full content available after enrollment";
                    }

                    return $material;
                });
            }

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error browsing materials",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get only materials from enrolled courses (full access)
     */
    public function myMaterials()
    {
        try {
            $this->authorize("viewAny", Material::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin can access all materials
                $materials = Material::with("courseModule.course")->get();
            } elseif ($user->role === "instructor") {
                // Instructor materials from own courses
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course", function ($query) use (
                        $user,
                    ) {
                        $query->where("instructor_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "student") {
                // Student materials from enrolled courses
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function (
                        $query,
                    ) use ($user) {
                        $query->where("student_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "parent") {
                // Parent materials from children's enrolled courses
                $childrenIds = \App\Models\User::where(
                    "parent_id",
                    $user->id,
                )->pluck("id");
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function (
                        $query,
                    ) use ($childrenIds) {
                        $query->whereIn("student_id", $childrenIds);
                    })
                    ->get();
            } else {
                $materials = collect();
            }

            // Add full access indicator
            $materials = $materials->map(function ($material) {
                $material->can_access = true;
                $material->access_level = "full";
                return $material;
            });

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving my materials",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

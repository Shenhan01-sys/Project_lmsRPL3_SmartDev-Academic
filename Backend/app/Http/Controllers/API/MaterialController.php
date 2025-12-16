<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Penting: Tambahkan ini untuk hapus file fisik
use OpenApi\Annotations as OA;

class MaterialController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     * path="/api/v1/materials",
     * tags={"Materials"},
     * summary="Get all materials",
     * description="Retrieve materials based on user role (students see only materials from enrolled courses)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Materials retrieved successfully",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="module_id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Introduction to Programming"),
     * @OA\Property(property="material_type", type="string", example="file"),
     * @OA\Property(property="content_path", type="string", example="materials/intro.pdf"),
     * @OA\Property(property="description", type="string", example="Basic programming concepts")
     * )
     * )
     * ),
     * @OA\Response(response=403, description="Unauthorized"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function index()
    {
        try {
            $this->authorize("viewAny", Material::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                // Admin bisa lihat semua materials
                $materials = Material::with("courseModule.course")->get();
            } elseif ($user->role === "instructor") {
                // Instructor hanya bisa lihat materials dari course yang dia ajar
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
                    ->whereHas("courseModule.course.enrollments", function ($query) use ($user) {
                        $query->where("student_id", $user->id);
                    })
                    ->get();
            } elseif ($user->role === "parent") {
                // Parent bisa lihat materials dari course yang anaknya ikuti
                $childrenIds = \App\Models\User::where("parent_id", $user->id)->pluck("id");
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function ($query) use ($childrenIds) {
                        $query->whereIn("student_id", $childrenIds);
                    })
                    ->get();
            } else {
                $materials = collect();
            }

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error retrieving materials",
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/materials",
     * tags={"Materials"},
     * summary="Create new material",
     * description="Create a new learning material. Supports file upload or URL link.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"module_id", "title", "material_type"},
     * @OA\Property(property="module_id", type="integer", example=1, description="ID of the Course Module"),
     * @OA\Property(property="title", type="string", example="Chapter 1 PDF"),
     * @OA\Property(property="material_type", type="string", enum={"file", "link", "video"}, example="file"),
     * @OA\Property(property="description", type="string", example="Optional description"),
     * @OA\Property(property="content_file", type="string", format="binary", description="Required if material_type is 'file'"),
     * @OA\Property(property="content_url", type="string", example="https://youtube.com/watch?v=...", description="Required if material_type is 'link' or 'video'")
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Material created successfully",
     * @OA\JsonContent(type="object")
     * ),
     * @OA\Response(response=400, description="Validation error"),
     * @OA\Response(response=403, description="Unauthorized"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize("create", Material::class);

        // 1. Validasi Umum
        $request->validate([
            "module_id" => "required|exists:course_modules,id",
            "title" => "required|string|max:255",
            "material_type" => "required|in:file,link,video",
            "description" => "nullable|string",
        ]);

        try {
            $contentPath = null;

            // 2. Logic Simpan Berdasarkan Tipe
            if ($request->material_type === 'file') {
                // Validasi File
                $request->validate([
                    'content_file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,jpg,png,mp4,mp3|max:51200' // Max 50MB
                ]);

                if ($request->hasFile('content_file')) {
                    // Upload ke folder 'public/materials'
                    $contentPath = $request->file('content_file')->store('materials', 'public');
                }
            } else {
                // Validasi Link/Video
                $request->validate([
                    'content_url' => 'required|string'
                ]);
                $contentPath = $request->content_url;
            }

            // 3. Simpan ke Database
            // Pastikan menggunakan nama kolom yang benar di database (misal: module_id atau course_module_id)
            // Di model Material Anda sebelumnya tertulis 'course_module_id' atau 'module_id' 
            // Sesuaikan key di array create di bawah ini dengan struktur tabel Anda.
            // Asumsi: kolom di tabel adalah 'module_id' (sesuai $fillable di model Material sebelumnya)
            
            $material = Material::create([
                'module_id' => $request->module_id, 
                'title' => $request->title,
                'material_type' => $request->material_type,
                'content_path' => $contentPath,
                'description' => $request->description
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
     * @OA\Get(
     * path="/api/v1/materials/{id}",
     * tags={"Materials"},
     * summary="Get material details",
     * description="Retrieve a specific material",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Material ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(type="object")
     * ),
     * @OA\Response(response=404, description="Material not found")
     * )
     */
    public function show(Material $material)
    {
        $this->authorize("view", $material);
        $material->load("courseModule");
        return response()->json($material);
    }

    /**
     * @OA\Post(
     * path="/api/v1/materials/{id}",
     * tags={"Materials"},
     * summary="Update material",
     * description="Update an existing material. Note: Use POST with _method=PUT to handle file uploads properly in Laravel.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Material ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="_method", type="string", example="PUT", description="Method spoofing for Laravel"),
     * @OA\Property(property="module_id", type="integer"),
     * @OA\Property(property="title", type="string"),
     * @OA\Property(property="material_type", type="string", enum={"file", "link", "video"}),
     * @OA\Property(property="description", type="string"),
     * @OA\Property(property="content_file", type="string", format="binary", description="Optional: Upload new file to replace old one"),
     * @OA\Property(property="content_url", type="string", description="Optional: New URL")
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Material updated successfully",
     * @OA\JsonContent(type="object")
     * ),
     * @OA\Response(response=403, description="Unauthorized"),
     * @OA\Response(response=404, description="Material not found"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, Material $material)
    {
        $this->authorize("update", $material);

        $request->validate([
            "module_id" => "sometimes|required|exists:course_modules,id",
            "title" => "sometimes|required|string|max:255",
            "material_type" => "sometimes|required|in:file,link,video",
            "description" => "nullable|string",
        ]);

        try {
            $data = $request->only(['module_id', 'title', 'material_type', 'description']);
            
            // Cek apakah ada perubahan konten (File atau URL)
            if ($request->has('material_type')) {
                $newType = $request->material_type;
                
                if ($newType === 'file' && $request->hasFile('content_file')) {
                    // 1. Hapus file lama jika ada dan tipe sebelumnya juga file
                    if ($material->material_type === 'file' && $material->content_path) {
                        if (Storage::disk('public')->exists($material->content_path)) {
                            Storage::disk('public')->delete($material->content_path);
                        }
                    }
                    
                    // 2. Upload file baru
                    $data['content_path'] = $request->file('content_file')->store('materials', 'public');
                    
                } elseif (($newType === 'link' || $newType === 'video') && $request->has('content_url')) {
                    // Jika ganti ke link, path diisi URL baru
                    // (Opsional: Hapus file lama jika sebelumnya tipe file)
                    if ($material->material_type === 'file' && $material->content_path) {
                        if (Storage::disk('public')->exists($material->content_path)) {
                            Storage::disk('public')->delete($material->content_path);
                        }
                    }
                    $data['content_path'] = $request->content_url;
                }
            }

            $material->update($data);
            return response()->json($material);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error updating material",
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     * path="/api/v1/materials/{id}",
     * tags={"Materials"},
     * summary="Delete material",
     * description="Delete a material and its associated file from storage",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Material ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Material deleted successfully"
     * ),
     * @OA\Response(response=403, description="Unauthorized"),
     * @OA\Response(response=404, description="Material not found"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Material $material)
    {
        $this->authorize("delete", $material);

        try {
            // Hapus file fisik jika tipe materi adalah 'file'
            if ($material->material_type === 'file' && $material->content_path) {
                if (Storage::disk('public')->exists($material->content_path)) {
                    Storage::disk('public')->delete($material->content_path);
                }
            }

            $material->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error deleting material",
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/materials/browse",
     * tags={"Materials"},
     * summary="Browse materials (preview)",
     * description="Browse materials with restricted access for non-enrolled users",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(type="array", @OA\Items(type="object"))
     * ),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function browse()
    {
        try {
            $this->authorize("viewAny", Material::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                $materials = Material::with("courseModule.course")->get();
                return $materials->map(function ($material) {
                    $material->can_access = true;
                    $material->access_level = "full";
                    return $material;
                });
            } elseif ($user->role === "instructor") {
                $materials = Material::with("courseModule.course")->get();
                return $materials->map(function ($material) use ($user) {
                    $canAccess = $material->courseModule->course->instructor_id === $user->id;
                    $material->can_access = $canAccess;
                    $material->access_level = $canAccess ? "full" : "preview";

                    if (!$canAccess) {
                        $material->content_path = "Preview available after course enrollment";
                    }
                    return $material;
                });
            } elseif (in_array($user->role, ["student", "parent"])) {
                $materials = Material::with("courseModule.course")->get();
                
                $enrolledCourseIds = collect();
                if ($user->role === "student") {
                    $enrolledCourseIds = Enrollment::where("student_id", $user->id)->pluck("course_id");
                } else {
                    $childrenIds = \App\Models\User::where("parent_id", $user->id)->pluck("id");
                    $enrolledCourseIds = Enrollment::whereIn("student_id", $childrenIds)
                        ->pluck("course_id")
                        ->unique();
                }

                return $materials->map(function ($material) use ($enrolledCourseIds) {
                    $canAccess = $enrolledCourseIds->contains($material->courseModule->course_id);
                    $material->can_access = $canAccess;
                    $material->access_level = $canAccess ? "full" : "preview";

                    if (!$canAccess) {
                        $material->content_path = "Enroll in course to access this material";
                        $material->description = "Preview: " . $material->title . " - Full content available after enrollment";
                    }
                    return $material;
                });
            }

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error browsing materials",
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/materials/my-materials",
     * tags={"Materials"},
     * summary="Get my materials",
     * description="Get materials from enrolled/taught courses only",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(type="array", @OA\Items(type="object"))
     * ),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function myMaterials()
    {
        try {
            $this->authorize("viewAny", Material::class);

            $user = Auth::user();

            if ($user->role === "admin") {
                $materials = Material::with("courseModule.course")->get();
            } elseif ($user->role === "instructor") {
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course", function ($query) use ($user) {
                        $query->where("instructor_id", $user->id);
                    })->get();
            } elseif ($user->role === "student") {
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function ($query) use ($user) {
                        $query->where("student_id", $user->id);
                    })->get();
            } elseif ($user->role === "parent") {
                $childrenIds = \App\Models\User::where("parent_id", $user->id)->pluck("id");
                $materials = Material::with("courseModule.course")
                    ->whereHas("courseModule.course.enrollments", function ($query) use ($childrenIds) {
                        $query->whereIn("student_id", $childrenIds);
                    })->get();
            } else {
                $materials = collect();
            }

            $materials = $materials->map(function ($material) {
                $material->can_access = true;
                $material->access_level = "full";
                return $material;
            });

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error retrieving my materials",
                "error" => $e->getMessage(),
            ], 500);
        }
    }
}
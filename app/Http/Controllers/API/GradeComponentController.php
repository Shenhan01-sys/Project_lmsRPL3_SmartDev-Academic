<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Services\GradingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class GradeComponentController extends Controller
{
    use AuthorizesRequests;

    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * @OA\Get(
     *     path="/api/grade-components",
     *     tags={"Grade Components"},
     *     summary="Get grade components for a course",
     *     description="Retrieve all grade components for a specific course with weight validation",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade components retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Komponen nilai berhasil diambil."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="weight_validation", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            "course_id" => "required|exists:courses,id",
        ]);

        try {
            // Authorization: Check if user can view grade components
            $this->authorize("viewAny", GradeComponent::class);

            $components = GradeComponent::where(
                "course_id",
                $request->course_id,
            )
                ->with("course:id,course_name")
                ->orderBy("created_at")
                ->get();

            // Ambil validasi total bobot
            $weightValidation = $this->gradingService->validateTotalWeight(
                $request->course_id,
            );

            return response()->json([
                "message" => "Komponen nilai berhasil diambil.",
                "data" => $components,
                "weight_validation" => $weightValidation,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error mengambil komponen nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/grade-components",
     *     tags={"Grade Components"},
     *     summary="Create a new grade component",
     *     description="Create a new grade component for a course with weight validation",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id", "name", "weight"},
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Mid-Term Exam"),
     *             @OA\Property(property="description", type="string", example="Midterm examination"),
     *             @OA\Property(property="weight", type="number", format="float", example=30.0),
     *             @OA\Property(property="max_score", type="number", format="float", example=100),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Grade component created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Komponen nilai berhasil dibuat."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "course_id" => "required|exists:courses,id",
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "weight" => "required|numeric|min:0|max:100",
            "max_score" => "nullable|numeric|min:0",
            "is_active" => "boolean",
        ]);

        try {
            // Authorization: Check if user can create grade components
            $this->authorize("create", GradeComponent::class);

            $component = $this->gradingService->createGradeComponent(
                $validated["course_id"],
                $validated,
            );

            return response()->json(
                [
                    "message" => "Komponen nilai berhasil dibuat.",
                    "data" => $component,
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Gagal membuat komponen nilai.",
                    "error" => $e->getMessage(),
                ],
                400,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/grade-components/{id}",
     *     tags={"Grade Components"},
     *     summary="Get grade component by ID",
     *     description="Retrieve a specific grade component with its grades",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Grade Component ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade component retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Detail komponen nilai berhasil diambil."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Grade component not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(GradeComponent $gradeComponent)
    {
        try {
            // Authorization: Check if user can view this grade component
            $this->authorize("view", $gradeComponent);

            $gradeComponent->load([
                "course:id,course_name",
                "grades.student:id,name",
            ]);

            return response()->json([
                "message" => "Detail komponen nilai berhasil diambil.",
                "data" => $gradeComponent,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error mengambil detail komponen nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/grade-components/{id}",
     *     tags={"Grade Components"},
     *     summary="Update grade component",
     *     description="Update an existing grade component with weight validation",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Grade Component ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Final Exam"),
     *             @OA\Property(property="description", type="string", example="Final examination"),
     *             @OA\Property(property="weight", type="number", format="float", example=40.0),
     *             @OA\Property(property="max_score", type="number", format="float", example=100),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade component updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Komponen nilai berhasil diupdate."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Weight validation error"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Grade component not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, GradeComponent $gradeComponent)
    {
        $validated = $request->validate([
            "name" => "sometimes|required|string|max:255",
            "description" => "nullable|string",
            "weight" => "sometimes|required|numeric|min:0|max:100",
            "max_score" => "sometimes|required|numeric|min:0",
            "is_active" => "boolean",
        ]);

        try {
            // Authorization: Check if user can update this grade component
            $this->authorize("update", $gradeComponent);

            // Validasi bobot jika ada perubahan
            if (isset($validated["weight"])) {
                $existingWeight = GradeComponent::where(
                    "course_id",
                    $gradeComponent->course_id,
                )
                    ->where("is_active", true)
                    ->where("id", "!=", $gradeComponent->id)
                    ->sum("weight");

                if ($existingWeight + $validated["weight"] > 100) {
                    return response()->json(
                        [
                            "message" => "Total bobot melebihi 100%.",
                            "error" =>
                                "Sisa bobot yang tersedia: " .
                                (100 - $existingWeight) .
                                "%",
                        ],
                        400,
                    );
                }
            }

            $gradeComponent->update($validated);

            return response()->json([
                "message" => "Komponen nilai berhasil diupdate.",
                "data" => $gradeComponent,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error update komponen nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/grade-components/{id}",
     *     tags={"Grade Components"},
     *     summary="Delete grade component",
     *     description="Remove a grade component (only if no grades exist)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Grade Component ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade component deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Komponen nilai berhasil dihapus.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cannot delete component with existing grades"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Grade component not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(GradeComponent $gradeComponent)
    {
        try {
            // Authorization: Check if user can delete this grade component
            $this->authorize("delete", $gradeComponent);

            // Check apakah ada grades yang sudah di-input
            $hasGrades = $gradeComponent->grades()->exists();

            if ($hasGrades) {
                return response()->json(
                    [
                        "message" =>
                            "Tidak dapat menghapus komponen nilai yang sudah memiliki data nilai.",
                    ],
                    400,
                );
            }

            $gradeComponent->delete();

            return response()->json([
                "message" => "Komponen nilai berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error menghapus komponen nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

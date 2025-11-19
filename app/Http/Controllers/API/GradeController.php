<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Services\GradingService;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class GradeController extends Controller
{
    use AuthorizesRequests;

    protected $gradingService;
    protected $enrollmentService;

    public function __construct(
        GradingService $gradingService,
        EnrollmentService $enrollmentService,
    ) {
        $this->gradingService = $gradingService;
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * @OA\Post(
     *     path="/api/grades",
     *     tags={"Grades"},
     *     summary="Input nilai siswa",
     *     description="Create a new grade entry for a student in a specific grade component",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "grade_component_id", "score"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="grade_component_id", type="integer", example=1),
     *             @OA\Property(property="score", type="number", format="float", example=85.5),
     *             @OA\Property(property="max_score", type="number", format="float", example=100),
     *             @OA\Property(property="notes", type="string", example="Good performance")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Grade created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nilai berhasil di-input."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error or enrollment required"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "student_id" => "required|exists:students,id",
            "grade_component_id" => "required|exists:grade_components,id",
            "score" => "required|numeric|min:0",
            "max_score" => "nullable|numeric|min:0",
            "notes" => "nullable|string",
        ]);

        try {
            // Authorization: Check if user can create grades
            $this->authorize("create", Grade::class);

            // VALIDASI: Pastikan student enrolled di course dari grade component ini
            if (
                !$this->enrollmentService->isStudentEnrolledInGradeComponentCourse(
                    $validated["student_id"],
                    $validated["grade_component_id"],
                )
            ) {
                return response()->json(
                    [
                        "message" => "Student is not enrolled in this course.",
                        "error" => "ENROLLMENT_REQUIRED",
                    ],
                    400,
                );
            }

            $options = [
                "max_score" => $validated["max_score"] ?? null,
                "notes" => $validated["notes"] ?? null,
                "graded_by" => Auth::id(),
            ];

            $grade = $this->gradingService->inputGrade(
                $validated["student_id"],
                $validated["grade_component_id"],
                $validated["score"],
                $options,
            );

            $grade->load([
                "student:id,full_name,student_number",
                "gradeComponent:id,name,weight",
                "grader:id,name",
            ]);

            return response()->json(
                [
                    "message" => "Nilai berhasil di-input.",
                    "data" => $grade,
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Gagal input nilai.",
                    "error" => $e->getMessage(),
                ],
                400,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/grades/bulk",
     *     tags={"Grades"},
     *     summary="Input nilai massal (bulk)",
     *     description="Create multiple grade entries at once for multiple students",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"grades"},
     *             @OA\Property(
     *                 property="grades",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="student_id", type="integer", example=1),
     *                     @OA\Property(property="grade_component_id", type="integer", example=1),
     *                     @OA\Property(property="score", type="number", format="float", example=85.5),
     *                     @OA\Property(property="max_score", type="number", format="float", example=100),
     *                     @OA\Property(property="notes", type="string", example="Good work")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bulk grades created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nilai massal berhasil di-input."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="count", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error or enrollment issues"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            "grades" => "required|array",
            "grades.*.student_id" => "required|exists:students,id",
            "grades.*.grade_component_id" =>
                "required|exists:grade_components,id",
            "grades.*.score" => "required|numeric|min:0",
            "grades.*.max_score" => "nullable|numeric|min:0",
            "grades.*.notes" => "nullable|string",
        ]);

        try {
            // Authorization: Check if user can bulk input grades
            $this->authorize("bulkInput", Grade::class);

            // VALIDASI: Pastikan semua student enrolled di course masing-masing
            $invalidEntries = [];
            foreach ($validated["grades"] as $index => $gradeData) {
                if (
                    !$this->enrollmentService->isStudentEnrolledInGradeComponentCourse(
                        $gradeData["student_id"],
                        $gradeData["grade_component_id"],
                    )
                ) {
                    $invalidEntries[] = [
                        "index" => $index,
                        "student_id" => $gradeData["student_id"],
                        "grade_component_id" =>
                            $gradeData["grade_component_id"],
                        "reason" => "Student not enrolled in this course",
                    ];
                }
            }

            if (!empty($invalidEntries)) {
                return response()->json(
                    [
                        "message" =>
                            "Some students are not enrolled in the required courses.",
                        "error" => "ENROLLMENT_REQUIRED",
                        "invalid_entries" => $invalidEntries,
                    ],
                    400,
                );
            }

            // Prepare data dengan options
            $gradesData = collect($validated["grades"])
                ->map(function ($grade) {
                    return [
                        "student_id" => $grade["student_id"],
                        "grade_component_id" => $grade["grade_component_id"],
                        "score" => $grade["score"],
                        "options" => [
                            "max_score" => $grade["max_score"] ?? null,
                            "notes" => $grade["notes"] ?? null,
                            "graded_by" => Auth::id(),
                        ],
                    ];
                })
                ->toArray();

            $grades = $this->gradingService->bulkInputGrades($gradesData);

            return response()->json(
                [
                    "message" => "Nilai massal berhasil di-input.",
                    "data" => $grades,
                    "count" => $grades->count(),
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Gagal input nilai massal.",
                    "error" => $e->getMessage(),
                ],
                400,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/grades/student",
     *     tags={"Grades"},
     *     summary="Get nilai siswa untuk course",
     *     description="Retrieve all grades for a student in a specific course including final grade",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student grades retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nilai siswa berhasil diambil."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="grades", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="final_grade", type="number", format="float", example=87.5)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getStudentGrades(Request $request)
    {
        $validated = $request->validate([
            "student_id" => "required|exists:students,id",
            "course_id" => "required|exists:courses,id",
        ]);

        try {
            // Authorization: Check if user can view grades
            $this->authorize("viewAny", Grade::class);

            $grades = $this->gradingService->getStudentGrades(
                $validated["student_id"],
                $validated["course_id"],
            );

            $finalGrade = $this->gradingService->calculateFinalGrade(
                $validated["student_id"],
                $validated["course_id"],
            );

            return response()->json([
                "message" => "Nilai siswa berhasil diambil.",
                "data" => [
                    "grades" => $grades,
                    "final_grade" => $finalGrade,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error mengambil nilai siswa.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/grades/course",
     *     tags={"Grades"},
     *     summary="Get rekap nilai untuk course",
     *     description="Retrieve grades summary and statistics for a specific course",
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
     *         description="Course grades summary retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rekap nilai course berhasil diambil."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="summary", type="object"),
     *                 @OA\Property(property="statistics", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getCourseGrades(Request $request)
    {
        $validated = $request->validate([
            "course_id" => "required|exists:courses,id",
        ]);

        try {
            // Authorization: Check if user can view course grades
            $this->authorize("viewStatistics", Grade::class);

            $summary = $this->gradingService->getCourseGradesSummary(
                $validated["course_id"],
            );
            $statistics = $this->gradingService->getCourseStatistics(
                $validated["course_id"],
            );

            return response()->json([
                "message" => "Rekap nilai course berhasil diambil.",
                "data" => [
                    "summary" => $summary,
                    "statistics" => $statistics,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error mengambil rekap nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/grades/{id}",
     *     tags={"Grades"},
     *     summary="Update nilai siswa",
     *     description="Update an existing grade entry",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Grade ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="score", type="number", format="float", example=90.0),
     *             @OA\Property(property="max_score", type="number", format="float", example=100),
     *             @OA\Property(property="notes", type="string", example="Improved performance")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nilai berhasil diupdate."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Grade not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            "score" => "sometimes|required|numeric|min:0",
            "max_score" => "sometimes|required|numeric|min:0",
            "notes" => "nullable|string",
        ]);

        try {
            // Authorization: Check if user can update this grade
            $this->authorize("update", $grade);

            // Validasi score tidak melebihi max_score
            if (isset($validated["score"]) && isset($validated["max_score"])) {
                if ($validated["score"] > $validated["max_score"]) {
                    return response()->json(
                        [
                            "message" =>
                                "Nilai tidak boleh melebihi nilai maksimal.",
                        ],
                        400,
                    );
                }
            } elseif (isset($validated["score"])) {
                if ($validated["score"] > $grade->max_score) {
                    return response()->json(
                        [
                            "message" =>
                                "Nilai tidak boleh melebihi nilai maksimal.",
                        ],
                        400,
                    );
                }
            }

            $grade->update($validated);
            $grade->load([
                "student:id,full_name,student_number",
                "gradeComponent:id,name,weight",
                "grader:id,name",
            ]);

            return response()->json([
                "message" => "Nilai berhasil diupdate.",
                "data" => $grade,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error update nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/grades/{id}",
     *     tags={"Grades"},
     *     summary="Delete nilai siswa",
     *     description="Remove a grade entry",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Grade ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nilai berhasil dihapus.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Grade not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Grade $grade)
    {
        try {
            // Authorization: Check if user can delete this grade
            $this->authorize("delete", $grade);

            $grade->delete();

            return response()->json([
                "message" => "Nilai berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error menghapus nilai.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

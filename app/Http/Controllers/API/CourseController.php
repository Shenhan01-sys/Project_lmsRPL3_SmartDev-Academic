<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class CourseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/courses",
     *     summary="Get all courses",
     *     description="Retrieve a list of all courses with instructor information",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="course_code", type="string", example="MTK101"),
     *                 @OA\Property(property="course_name", type="string", example="Matematika Dasar"),
     *                 @OA\Property(property="description", type="string", example="Mempelajari konsep dasar matematika"),
     *                 @OA\Property(property="instructor_id", type="integer", example=1),
     *                 @OA\Property(property="credits", type="integer", example=3),
     *                 @OA\Property(property="max_students", type="integer", example=30),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize("viewAny", Course::class);
        try {
            $courses = Course::with("instructor")->get();
            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error retrieving courses",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/v1/courses",
     *     summary="Create new course",
     *     description="Create a new course (Admin/Instructor only)",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_code", "course_name", "instructor_id"},
     *             @OA\Property(property="course_code", type="string", example="MTK101"),
     *             @OA\Property(property="course_name", type="string", example="Matematika Dasar"),
     *             @OA\Property(property="description", type="string", example="Mempelajari konsep dasar matematika"),
     *             @OA\Property(property="instructor_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="course_code", type="string", example="MTK101"),
     *             @OA\Property(property="course_name", type="string", example="Matematika Dasar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Authorization: Check if user can create courses
        $this->authorize("create", Course::class);

        $validated = $request->validate([
            "course_code" => "required|string|unique:courses,course_code",
            "course_name" => "required|string|max:255",
            "description" => "nullable|string",
            "instructor_id" => "required|exists:instructors,id",
        ]);

        try {
            $course = Course::create($validated);
            return response()->json($course, 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error creating course",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/v1/courses/{id}",
     *     summary="Get course details",
     *     description="Get detailed information about a specific course including modules, materials, and assignments",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="course_code", type="string", example="MTK101"),
     *             @OA\Property(property="course_name", type="string", example="Matematika Dasar"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="instructor", type="object"),
     *             @OA\Property(property="enrollments", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="courseModules", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="assignments", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     )
     * )
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        // Authorization: Check if user can view this course
        $this->authorize("view", $course);

        $course->load(
            "instructor.user",
            "enrollments.student.user",
            "courseModules.materials",
            "assignments",
        );
        return response()->json($course);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/v1/courses/{id}",
     *     summary="Update course",
     *     description="Update an existing course (Admin/Instructor only)",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="course_code", type="string", example="MTK101"),
     *             @OA\Property(property="course_name", type="string", example="Matematika Dasar"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="instructor_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        // Authorization: Check if user can update this course
        $this->authorize("update", $course);

        $validated = $request->validate([
            "course_code" => [
                "sometimes",
                "required",
                "string",
                Rule::unique("courses")->ignore($course->id),
            ],
            "course_name" => "sometimes|required|string|max:255",
            "description" => "nullable|string",
            "instructor_id" => "sometimes|required|exists:instructors,id",
        ]);

        try {
            $course->update($validated);
            return response()->json($course);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error updating course",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        // Authorization: Check if user can delete this course
        $this->authorize("delete", $course);

        try {
            $course->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Error deleting course",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}

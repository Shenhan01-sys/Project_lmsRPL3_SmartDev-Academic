<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class StudentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/students",
     *     tags={"Students"},
     *     summary="Get all students",
     *     description="Retrieve a list of all students with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by student status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "graduated", "dropped"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, email, or student number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'parent']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('student_number', 'like', "%$search%");
            });
        }

        $students = $query->paginate($request->get('per_page', 15));

        return response()->json($students);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/students",
     *     tags={"Students"},
     *     summary="Create new student",
     *     description="Create a new student record and associated user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_number", "full_name", "email", "password"},
     *             @OA\Property(property="student_number", type="string", example="S2023001"),
     *             @OA\Property(property="full_name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="08123456789"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="2005-01-01"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female"}),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="emergency_contact_name", type="string"),
     *             @OA\Property(property="emergency_contact_phone", type="string"),
     *             @OA\Property(property="parent_id", type="integer"),
     *             @OA\Property(property="enrollment_year", type="integer", example=2023),
     *             @OA\Property(property="current_grade", type="string", example="10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Student created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student created successfully"),
     *             @OA\Property(property="student", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|unique:students,student_number',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:parents,id',
            'enrollment_year' => 'nullable|integer',
            'current_grade' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Create user account for login
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'student',
            ]);

            // Create student profile
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $validated['student_number'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'parent_id' => $validated['parent_id'] ?? null,
                'enrollment_year' => $validated['enrollment_year'] ?? date('Y'),
                'current_grade' => $validated['current_grade'] ?? null,
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Student created successfully',
                'student' => $student->load(['user', 'parent']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/{id}",
     *     tags={"Students"},
     *     summary="Get student details",
     *     description="Get detailed information about a specific student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function show(string $id)
    {
        $student = Student::with(['user', 'parent', 'enrollments.course', 'submissions'])
            ->findOrFail($id);

        return response()->json($student);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/students/{id}",
     *     tags={"Students"},
     *     summary="Update student",
     *     description="Update an existing student record",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="date_of_birth", type="string", format="date"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female"}),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="emergency_contact_name", type="string"),
     *             @OA\Property(property="emergency_contact_phone", type="string"),
     *             @OA\Property(property="parent_id", type="integer"),
     *             @OA\Property(property="current_grade", type="string"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "graduated", "dropped"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student updated successfully"),
     *             @OA\Property(property="student", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Student not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:students,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:parents,id',
            'current_grade' => 'nullable|string|max:50',
            'status' => 'sometimes|in:active,inactive,graduated,dropped',
        ]);

        $student->update($validated);

        // Update user email if changed
        if (isset($validated['email']) && $student->user->email !== $validated['email']) {
            $student->user->update(['email' => $validated['email']]);
        }

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student->load(['user', 'parent']),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/students/{id}",
     *     tags={"Students"},
     *     summary="Delete student",
     *     description="Delete a student record and associated user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Student not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // This will cascade delete the user account
            $student->user->delete();
            
            DB::commit();

            return response()->json([
                'message' => 'Student deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/{id}/enrollments",
     *     tags={"Students"},
     *     summary="Get student enrollments",
     *     description="Get all courses a student is enrolled in",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function enrollments(string $id)
    {
        $student = Student::findOrFail($id);
        $enrollments = $student->enrollments()->with('course.instructor')->get();

        return response()->json($enrollments);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/{id}/submissions",
     *     tags={"Students"},
     *     summary="Get student submissions",
     *     description="Get all assignment submissions for a student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function submissions(string $id)
    {
        $student = Student::findOrFail($id);
        $submissions = $student->submissions()->with('assignment.course')->get();

        return response()->json($submissions);
    }
}

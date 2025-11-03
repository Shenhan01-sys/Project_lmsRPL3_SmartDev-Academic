<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with(['user', 'parent', 'enrollments.course', 'submissions'])
            ->findOrFail($id);

        return response()->json($student);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
     * Get student enrollments
     */
    public function enrollments(string $id)
    {
        $student = Student::findOrFail($id);
        $enrollments = $student->enrollments()->with('course.instructor')->get();

        return response()->json($enrollments);
    }

    /**
     * Get student submissions
     */
    public function submissions(string $id)
    {
        $student = Student::findOrFail($id);
        $submissions = $student->submissions()->with('assignment.course')->get();

        return response()->json($submissions);
    }
}

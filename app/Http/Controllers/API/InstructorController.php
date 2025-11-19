<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Instructor::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or specialization
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('specialization', 'like', "%$search%")
                  ->orWhere('instructor_code', 'like', "%$search%");
            });
        }

        $instructors = $query->paginate($request->get('per_page', 15));

        return response()->json($instructors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_code' => 'required|string|unique:instructors,instructor_code',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:instructors,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'bio' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create user account for login
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'instructor',
            ]);

            // Create instructor profile
            $instructor = Instructor::create([
                'user_id' => $user->id,
                'instructor_code' => $validated['instructor_code'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'experience_years' => $validated['experience_years'] ?? 0,
                'bio' => $validated['bio'] ?? null,
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Instructor created successfully',
                'instructor' => $instructor->load('user'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create instructor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $instructor = Instructor::with(['user', 'courses'])
            ->findOrFail($id);

        return response()->json($instructor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $instructor = Instructor::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:instructors,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'bio' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,resigned',
        ]);

        $instructor->update($validated);

        // Update user email if changed
        if (isset($validated['email']) && $instructor->user->email !== $validated['email']) {
            $instructor->user->update(['email' => $validated['email']]);
        }

        return response()->json([
            'message' => 'Instructor updated successfully',
            'instructor' => $instructor->load('user'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $instructor = Instructor::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // This will cascade delete the user account
            $instructor->user->delete();
            
            DB::commit();

            return response()->json([
                'message' => 'Instructor deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete instructor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get instructor's courses
     */
    public function courses(string $id)
    {
        $instructor = Instructor::findOrFail($id);
        $courses = $instructor->courses()->with('enrollments')->get();

        return response()->json($courses);
    }

    /**
     * Get instructor's active courses
     */
    public function activeCourses(string $id)
    {
        $instructor = Instructor::findOrFail($id);
        $courses = $instructor->activeCourses()->with('enrollments')->get();

        return response()->json($courses);
    }
}

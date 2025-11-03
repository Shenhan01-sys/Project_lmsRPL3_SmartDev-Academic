<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ParentModel::with('user');

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        $parents = $query->paginate($request->get('per_page', 15));

        return response()->json($parents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:parents,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'relationship' => 'required|in:father,mother,guardian',
            'occupation' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create user account for login
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'parent',
            ]);

            // Create parent profile
            $parent = ParentModel::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'relationship' => $validated['relationship'],
                'occupation' => $validated['occupation'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Parent created successfully',
                'parent' => $parent->load('user'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parent = ParentModel::with(['user', 'students'])
            ->findOrFail($id);

        return response()->json($parent);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $parent = ParentModel::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:parents,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'relationship' => 'sometimes|in:father,mother,guardian',
            'occupation' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $parent->update($validated);

        // Update user email if changed
        if (isset($validated['email']) && $parent->user->email !== $validated['email']) {
            $parent->user->update(['email' => $validated['email']]);
        }

        return response()->json([
            'message' => 'Parent updated successfully',
            'parent' => $parent->load('user'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $parent = ParentModel::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // This will cascade delete the user account
            $parent->user->delete();
            
            DB::commit();

            return response()->json([
                'message' => 'Parent deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parent's students (children)
     */
    public function students(string $id)
    {
        $parent = ParentModel::findOrFail($id);
        $students = $parent->students()->with(['enrollments.course'])->get();

        return response()->json($students);
    }

    /**
     * Get parent's active students
     */
    public function activeStudents(string $id)
    {
        $parent = ParentModel::findOrFail($id);
        $students = $parent->activeStudents()->with(['enrollments.course'])->get();

        return response()->json($students);
    }
}

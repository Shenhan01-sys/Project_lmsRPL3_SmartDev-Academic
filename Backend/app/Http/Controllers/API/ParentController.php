<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class ParentController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/parents",
     *     tags={"Parents"},
     *     summary="Get all parents",
     *     description="Retrieve a list of all parents with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, email, or phone",
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
     * @OA\Post(
     *     path="/api/v1/parents",
     *     tags={"Parents"},
     *     summary="Create new parent",
     *     description="Create a new parent record and associated user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name", "email", "password", "relationship"},
     *             @OA\Property(property="full_name", type="string", example="John Doe Sr."),
     *             @OA\Property(property="email", type="string", format="email", example="john.sr@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="08123456789"),
     *             @OA\Property(property="relationship", type="string", enum={"father", "mother", "guardian"}),
     *             @OA\Property(property="occupation", type="string", example="Engineer"),
     *             @OA\Property(property="address", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Parent created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Parent created successfully"),
     *             @OA\Property(property="parent", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
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
     * @OA\Get(
     *     path="/api/v1/parents/{id}",
     *     tags={"Parents"},
     *     summary="Get parent details",
     *     description="Get detailed information about a specific parent",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parent ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Parent not found")
     * )
     */
    public function show(string $id)
    {
        $parent = ParentModel::with(['user', 'students'])
            ->findOrFail($id);

        return response()->json($parent);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/parents/{id}",
     *     tags={"Parents"},
     *     summary="Update parent",
     *     description="Update an existing parent record",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parent ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="relationship", type="string", enum={"father", "mother", "guardian"}),
     *             @OA\Property(property="occupation", type="string"),
     *             @OA\Property(property="address", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parent updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Parent updated successfully"),
     *             @OA\Property(property="parent", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Parent not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
     * @OA\Delete(
     *     path="/api/v1/parents/{id}",
     *     tags={"Parents"},
     *     summary="Delete parent",
     *     description="Delete a parent record and associated user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parent ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parent deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Parent deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Parent not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
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
     * @OA\Get(
     *     path="/api/v1/parents/{id}/students",
     *     tags={"Parents"},
     *     summary="Get parent's children",
     *     description="Get all students associated with a parent",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parent ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="Parent not found")
     * )
     */
    public function students(string $id)
    {
        $parent = ParentModel::findOrFail($id);
        $students = $parent->students()->with(['enrollments.course'])->get();

        return response()->json($students);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/parents/{id}/active-students",
     *     tags={"Parents"},
     *     summary="Get parent's active children",
     *     description="Get all active students associated with a parent",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parent ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="Parent not found")
     * )
     */
    public function activeStudents(string $id)
    {
        $parent = ParentModel::findOrFail($id);
        $students = $parent->activeStudents()->with(['enrollments.course'])->get();

        return response()->json($students);
    }
}

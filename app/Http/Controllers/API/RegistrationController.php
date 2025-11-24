<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class RegistrationController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Post(
     *     path="/api/register/calon-siswa",
     *     tags={"Registration"},
     *     summary="Register as calon siswa",
     *     description="Register a new prospective student",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "nisn", "phone_number", "school_origin"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="nisn", type="string", example="1234567890"),
     *             @OA\Property(property="phone_number", type="string", example="08123456789"),
     *             @OA\Property(property="school_origin", type="string", example="SMP Negeri 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nisn' => 'required|string|unique:calon_siswas',
            'phone_number' => 'required|string',
            'school_origin' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'calon_siswa',
            ]);

            $calonSiswa = CalonSiswa::create([
                'user_id' => $user->id,
                'nisn' => $request->nisn,
                'phone_number' => $request->phone_number,
                'school_origin' => $request->school_origin,
                'registration_number' => 'REG-' . date('Ymd') . '-' . rand(1000, 9999),
                'status' => 'pending_documents',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil. Silakan upload dokumen yang diperlukan.',
                'data' => $calonSiswa,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registrasi gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register/upload-documents",
     *     tags={"Registration"},
     *     summary="Upload registration documents",
     *     description="Upload documents (ijazah, skhun, kk, akta) for registration",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="ijazah", type="string", format="binary", description="Ijazah file (PDF/Image)"),
     *                 @OA\Property(property="skhun", type="string", format="binary", description="SKHUN file (PDF/Image)"),
     *                 @OA\Property(property="kk", type="string", format="binary", description="Kartu Keluarga file (PDF/Image)"),
     *                 @OA\Property(property="akta_kelahiran", type="string", format="binary", description="Akta Kelahiran file (PDF/Image)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function uploadDocuments(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'calon_siswa') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $calonSiswa = $user->calonSiswa;

        $validator = Validator::make($request->all(), [
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'skhun' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'akta_kelahiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $documents = $calonSiswa->documents ?? [];

            if ($request->hasFile('ijazah')) {
                $path = $request->file('ijazah')->store('registration/ijazah', 'public');
                $documents['ijazah'] = $path;
            }
            if ($request->hasFile('skhun')) {
                $path = $request->file('skhun')->store('registration/skhun', 'public');
                $documents['skhun'] = $path;
            }
            if ($request->hasFile('kk')) {
                $path = $request->file('kk')->store('registration/kk', 'public');
                $documents['kk'] = $path;
            }
            if ($request->hasFile('akta_kelahiran')) {
                $path = $request->file('akta_kelahiran')->store('registration/akta', 'public');
                $documents['akta_kelahiran'] = $path;
            }

            $calonSiswa->update([
                'documents' => $documents,
                'status' => 'documents_uploaded'
            ]);

            return response()->json([
                'message' => 'Dokumen berhasil diupload',
                'data' => $calonSiswa
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload dokumen gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/register/status",
     *     tags={"Registration"},
     *     summary="Check registration status",
     *     description="Get current registration status of the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function checkStatus()
    {
        $user = Auth::user();
        
        if ($user->role !== 'calon_siswa') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => $user->calonSiswa->status,
            'data' => $user->calonSiswa
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/registrations",
     *     tags={"Registration"},
     *     summary="List registrations (Admin)",
     *     description="Get list of all registrations (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CalonSiswa::class);

        $query = CalonSiswa::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->latest()->paginate(20);

        return response()->json($registrations);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/registrations/{id}",
     *     tags={"Registration"},
     *     summary="Get registration details (Admin)",
     *     description="Get details of a specific registration (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Registration ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details retrieved successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Registration not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show($id)
    {
        $calonSiswa = CalonSiswa::with('user')->findOrFail($id);
        $this->authorize('view', $calonSiswa);

        return response()->json($calonSiswa);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/registrations/{id}/approve",
     *     tags={"Registration"},
     *     summary="Approve registration (Admin)",
     *     description="Approve a registration and create student account (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Registration ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="student", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Registration not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function approve($id)
    {
        $calonSiswa = CalonSiswa::findOrFail($id);
        $this->authorize('update', $calonSiswa);

        DB::beginTransaction();
        try {
            $calonSiswa->update(['status' => 'approved']);
            $user = $calonSiswa->user;
            $user->update(['role' => 'student']);

            // Create student profile
            $student = Student::create([
                'user_id' => $user->id,
                'nisn' => $calonSiswa->nisn,
                'phone_number' => $calonSiswa->phone_number,
                // Add other default fields
            ]);

            // Send notification email (simulated)
            
            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran disetujui. Akun siswa telah dibuat.',
                'student' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyetujui pendaftaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/registrations/{id}/reject",
     *     tags={"Registration"},
     *     summary="Reject registration (Admin)",
     *     description="Reject a registration (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Registration ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Documents incomplete")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Registration not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        
        $calonSiswa = CalonSiswa::findOrFail($id);
        $this->authorize('update', $calonSiswa);

        $calonSiswa->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        // Send notification email (simulated)

        return response()->json([
            'message' => 'Pendaftaran ditolak.'
        ]);
    }
}

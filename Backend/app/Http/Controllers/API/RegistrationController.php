<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StudentRegistration;
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
     *     path="/api/register-calon-siswa",
     *     tags={"Registration"},
     *     summary="Register as calon siswa",
     *     description="Register a new prospective student (Public endpoint)",
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
     *     @OA\Response(response=201, description="Registration successful"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function registerCalonSiswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|confirmed",
            "tanggal_lahir" => "required|date",
            "tempat_lahir" => "required|string|max:255",
            "jenis_kelamin" => "required|in:L,P",
            "nama_orang_tua" => "required|string|max:255",
            "email_orang_tua" => "nullable|email|max:255",
            "phone_orang_tua" => "required|string",
            "alamat_orang_tua" => "required|string",
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role" => "calon_siswa",
            ]);

            $registration = StudentRegistration::create([
                "user_id" => $user->id,
                "tanggal_lahir" => $request->tanggal_lahir,
                "tempat_lahir" => $request->tempat_lahir,
                "jenis_kelamin" => $request->jenis_kelamin,
                "nama_orang_tua" => $request->nama_orang_tua,
                "email_orang_tua" => $request->email_orang_tua,
                "phone_orang_tua" => $request->phone_orang_tua,
                "alamat_orang_tua" => $request->alamat_orang_tua,
                // "registration_status" => "pending", // Sudah ada default di database
                // "submitted_at" => now(), // Comment dulu jika kolom tidak ada di database
            ]);

            $token = $user->createToken("auth_token")->plainTextToken;

            DB::commit();

            return response()->json(
                [
                    "message" =>
                        "Registrasi berhasil.  Silakan upload dokumen yang diperlukan.",
                    "data" => $registration,
                    "token" => $token,
                ],
                201,
            );
        } catch (\Exception $e) {
            DB::rollBack();

            // Log error untuk debugging
            \Log::error("Registration Error: " . $e->getMessage(), [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTraceAsString(),
                "request_data" => $request->except([
                    "password",
                    "password_confirmation",
                ]),
            ]);

            return response()->json(
                [
                    "message" => "Registrasi gagal",
                    "error" => $e->getMessage(),
                    "details" => config("app.debug")
                        ? [
                            "file" => $e->getFile(),
                            "line" => $e->getLine(),
                        ]
                        : null,
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/upload-documents",
     *     tags={"Registration"},
     *     summary="Upload registration documents",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Documents uploaded successfully")
     * )
     */
    public function uploadDocuments(Request $request)
    {
        $user = Auth::user();
        $registration = $user->studentRegistration;

        if (!$registration) {
            return response()->json(
                ["message" => "Registration not found"],
                404,
            );
        }

        // ✅ Authorization check menggunakan Policy
        $this->authorize("uploadDocuments", $registration);

        $validator = Validator::make($request->all(), [
            "ktp_orang_tua" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:2048",
            "ijazah" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:2048",
            "foto_siswa" => "nullable|file|mimes:jpg,jpeg,png|max:2048",
            "bukti_pembayaran" =>
                "nullable|file|mimes:pdf,jpg,jpeg,png|max:2048",
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 422);
        }

        try {
            $updateData = [];

            if ($request->hasFile("ktp_orang_tua")) {
                $updateData["ktp_orang_tua_path"] = $request
                    ->file("ktp_orang_tua")
                    ->store("registration/ktp_orang_tua", "public");
            }

            if ($request->hasFile("ijazah")) {
                $updateData["ijazah_path"] = $request
                    ->file("ijazah")
                    ->store("registration/ijazah", "public");
            }

            if ($request->hasFile("foto_siswa")) {
                $updateData["foto_siswa_path"] = $request
                    ->file("foto_siswa")
                    ->store("registration/foto_siswa", "public");
            }

            if ($request->hasFile("bukti_pembayaran")) {
                $updateData["bukti_pembayaran_path"] = $request
                    ->file("bukti_pembayaran")
                    ->store("registration/bukti_pembayaran", "public");
            }

            if (!empty($updateData)) {
                // "registration_status" sudah ada default di database, tidak perlu di-set lagi
                $registration->update($updateData);
            }

            return response()->json([
                "message" => "Dokumen berhasil diupload",
                "data" => $registration,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Upload dokumen gagal",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/registration-registration_status",
     *     tags={"Registration"},
     *     summary="Check registration registration_status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="registration_status retrieved successfully")
     * )
     */
    public function getRegistrationregistration_status()
    {
        $user = Auth::user();

        // ✅ Authorization check menggunakan Policy
        $this->authorize(
            "checkregistration_status",
            StudentRegistration::class,
        );

        $registration = $user->studentRegistration;

        if (!$registration) {
            return response()->json(
                ["message" => "Registration not found"],
                404,
            );
        }

        return response()->json([
            "registration_status" => $registration->registration_status,
            "data" => $registration,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/registrations",
     *     tags={"Registration"},
     *     summary="List registrations (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List retrieved successfully")
     * )
     */
    public function index(Request $request)
    {
        // ✅ Authorization check - hanya admin
        $this->authorize("viewAny", StudentRegistration::class);

        $query = StudentRegistration::with("user");

        if ($request->has("registration_status")) {
            $query->where("registration_status", $request->registration_status);
        }

        $registrations = $query->latest()->paginate(20);

        return response()->json($registrations);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/registrations/{id}",
     *     tags={"Registration"},
     *     summary="Get registration details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Details retrieved successfully")
     * )
     */
    public function show($id)
    {
        $registration = StudentRegistration::with("user")->findOrFail($id);

        // ✅ Authorization check - admin atau pemilik registrasi
        $this->authorize("view", $registration);

        return response()->json($registration);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/registrations/{id}/approve",
     *     tags={"Registration"},
     *     summary="Approve registration and create student + parent accounts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Registration approved, accounts created")
     * )
     */
    public function approve($id)
    {
        $registration = StudentRegistration::with("user")->findOrFail($id);

        // ✅ Authorization check - hanya admin
        $this->authorize("update", $registration);

        // Validasi: pastikan data lengkap
        if (!$registration->nama_orang_tua) {
            return response()->json(
                [
                    "message" =>
                        "Data orang tua belum lengkap. Nama orang tua harus diisi.",
                ],
                422,
            );
        }

        // Generate email orang tua jika belum ada
        if (!$registration->email_orang_tua) {
            $emailPrefix = str_replace(
                " ",
                ".",
                strtolower($registration->nama_orang_tua),
            );
            $randomNumber = rand(100, 999);
            $registration->email_orang_tua = "{$emailPrefix}{$randomNumber}@parent.com";
            $registration->save();
        }

        DB::beginTransaction();
        try {
            $user = $registration->user;

            // 1. Update registration status
            $registration->update([
                "registration_status" => "approved",
                "approved_at" => now(),
                "approved_by" => Auth::id(),
            ]);

            // 2. Update user role dari calon_siswa menjadi student
            $user->update(["role" => "student"]);

            // 3. Create Student Account
            // Password default = nama lengkap siswa (tanpa spasi, lowercase)
            $studentPassword = str_replace(" ", "", strtolower($user->name));

            // Convert gender from L/P to male/female for database ENUM
            $gender = null;
            if ($registration->jenis_kelamin === "L") {
                $gender = "male";
            } elseif ($registration->jenis_kelamin === "P") {
                $gender = "female";
            }

            $student = Student::create([
                "user_id" => $user->id,
                "student_number" =>
                    "STD-" .
                    date("Ymd") .
                    "-" .
                    str_pad($registration->id, 4, "0", STR_PAD_LEFT),
                "full_name" => $user->name,
                "email" => $user->email,
                "phone" => $registration->phone_orang_tua ?? null,
                "date_of_birth" => $registration->tanggal_lahir,
                "place_of_birth" => $registration->tempat_lahir,
                "gender" => $gender,
                "address" => $registration->alamat_orang_tua,
                "status" => "active",
            ]);

            // Update password user siswa
            $user->update(["password" => Hash::make($studentPassword)]);

            // 4. Create Parent Account
            $parentPassword = str_replace(
                " ",
                "",
                strtolower($registration->nama_orang_tua),
            );

            // Check if parent user already exists
            $parentUser = User::where(
                "email",
                $registration->email_orang_tua,
            )->first();

            if (!$parentUser) {
                $parentUser = User::create([
                    "name" => $registration->nama_orang_tua,
                    "email" => $registration->email_orang_tua,
                    "password" => Hash::make($parentPassword),
                    "role" => "parent",
                ]);
            }

            // Check if ParentModel exists, if not skip parent creation
            if (class_exists(\App\Models\ParentModel::class)) {
                $parent = \App\Models\ParentModel::firstOrCreate(
                    ["user_id" => $parentUser->id],
                    [
                        "full_name" => $registration->nama_orang_tua,
                        "email" => $registration->email_orang_tua,
                        "phone" => $registration->phone_orang_tua,
                        "address" => $registration->alamat_orang_tua,
                        "relationship" => "father",
                    ],
                );

                // Link parent dengan student if StudentParent model exists
                if (class_exists(\App\Models\StudentParent::class)) {
                    \App\Models\StudentParent::firstOrCreate([
                        "student_id" => $student->id,
                        "parent_id" => $parent->id,
                        "relationship" => "primary_guardian",
                    ]);
                }
            }

            DB::commit();

            $responseData = [
                "message" =>
                    "Pendaftaran berhasil disetujui! Akun telah dibuat.",
                "data" => [
                    "student" => [
                        "name" => $student->full_name,
                        "email" => $student->email,
                        "student_number" => $student->student_number,
                        "default_password" => $studentPassword,
                    ],
                ],
            ];

            // Add parent info if it was created
            if (isset($parent)) {
                $responseData["message"] =
                    "Pendaftaran berhasil disetujui! 2 akun telah dibuat.";
                $responseData["data"]["parent"] = [
                    "name" => $registration->nama_orang_tua,
                    "email" => $registration->email_orang_tua,
                    "default_password" => $parentPassword,
                ];
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log detailed error untuk debugging
            \Log::error(
                "Error approving registration ID {$id}: " . $e->getMessage(),
                [
                    "exception" => get_class($e),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                    "trace" => $e->getTraceAsString(),
                    "registration_id" => $id,
                ],
            );

            return response()->json(
                [
                    "message" => "Gagal menyetujui pendaftaran",
                    "error" => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/registrations/{id}/reject",
     *     tags={"Registration"},
     *     summary="Reject registration (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Registration rejected")
     * )
     */
    public function reject(Request $request, $id)
    {
        $request->validate(["reason" => "required|string"]);

        $registration = StudentRegistration::findOrFail($id);

        // ✅ Authorization check - hanya admin
        $this->authorize("update", $registration);

        $registration->update([
            "registration_status" => "rejected",
            "rejection_reason" => $request->reason,
        ]);

        return response()->json([
            "message" => "Pendaftaran ditolak.",
        ]);
    }
}

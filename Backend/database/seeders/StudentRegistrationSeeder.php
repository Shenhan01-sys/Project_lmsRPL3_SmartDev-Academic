<?php

namespace Database\Seeders;

use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StudentRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users with calon_siswa role first
        $calonSiswaData = [
            [
                "name" => "Ahmad Fauzi",
                "email" => "ahmad.fauzi@example.com",
                "phone" => "081234567890",
            ],
            [
                "name" => "Siti Nurhaliza",
                "email" => "siti.nur@example.com",
                "phone" => "081234567891",
            ],
            [
                "name" => "Budi Santoso",
                "email" => "budi.santoso@example.com",
                "phone" => "081234567892",
            ],
            [
                "name" => "Dewi Lestari",
                "email" => "dewi.lestari@example.com",
                "phone" => "081234567893",
            ],
            [
                "name" => "Eko Prasetyo",
                "email" => "eko.prasetyo@example.com",
                "phone" => "081234567894",
            ],
            [
                "name" => "Fitri Handayani",
                "email" => "fitri.handayani@example.com",
                "phone" => "081234567895",
            ],
            [
                "name" => "Gilang Ramadhan",
                "email" => "gilang.ramadhan@example.com",
                "phone" => "081234567896",
            ],
            [
                "name" => "Hana Pertiwi",
                "email" => "hana.pertiwi@example.com",
                "phone" => "081234567897",
            ],
            [
                "name" => "Indra Gunawan",
                "email" => "indra.gunawan@example.com",
                "phone" => "081234567898",
            ],
            [
                "name" => "Joko Widodo",
                "email" => "joko.widodo@example.com",
                "phone" => "081234567899",
            ],
        ];

        $statusDistribution = [
            "pending_documents" => 2, // 2 users - just registered
            "pending_approval" => 4, // 4 users - documents uploaded
            "approved" => 3, // 3 users - approved (will be converted to student)
            "rejected" => 1, // 1 user - rejected
        ];

        $tempat_lahir = [
            "Jakarta",
            "Bandung",
            "Surabaya",
            "Medan",
            "Semarang",
            "Makassar",
            "Palembang",
            "Tangerang",
            "Depok",
            "Bekasi",
        ];
        $jenis_kelamin = ["L", "P"];

        $index = 0;
        foreach ($statusDistribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                if ($index >= count($calonSiswaData)) {
                    break;
                }

                $data = $calonSiswaData[$index];

                // Create user with calon_siswa role (except for approved ones)
                $user = User::create([
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "password" => bcrypt("password123"),
                    "role" =>
                        $status === "approved" ? "student" : "calon_siswa",
                    "level" => null,
                ]);

                // Generate registration data
                $tanggalLahir = Carbon::now()
                    ->subYears(rand(15, 18))
                    ->subMonths(rand(0, 11))
                    ->subDays(rand(0, 30));
                $tempatLahir = $tempat_lahir[array_rand($tempat_lahir)];
                $gender = $jenis_kelamin[array_rand($jenis_kelamin)];

                // Parent data
                $namaOrangTua = $this->generateParentName(
                    $data["name"],
                    $gender,
                );
                $phoneOrangTua = "08" . rand(1000000000, 9999999999);
                $alamatOrangTua = $this->generateAddress();

                // Documents (filled if not pending_documents)
                $ktpPath = null;
                $ijazahPath = null;
                $fotoPath = null;
                $buktiPembayaranPath = null;
                $submittedAt = null;

                if ($status !== "pending_documents") {
                    $ktpPath = "registrations/ktp/" . uniqid() . ".pdf";
                    $ijazahPath = "registrations/ijazah/" . uniqid() . ".pdf";
                    $fotoPath = "registrations/foto/" . uniqid() . ".jpg";
                    $buktiPembayaranPath =
                        "registrations/payment/" . uniqid() . ".jpg";
                    $submittedAt = Carbon::now()->subDays(rand(1, 30));
                }

                // Approval data
                $approvedAt = null;
                $approvedBy = null;
                $approvalNotes = null;

                if ($status === "approved") {
                    $admin = User::where("role", "admin")->first();
                    $approvedAt = $submittedAt
                        ? $submittedAt->copy()->addDays(rand(1, 5))
                        : Carbon::now()->subDays(rand(1, 10));
                    $approvedBy = $admin ? $admin->id : null;
                    $approvalNotes =
                        "Berkas lengkap dan memenuhi syarat. Selamat bergabung di SmartDev LMS!";
                } elseif ($status === "rejected") {
                    $admin = User::where("role", "admin")->first();
                    $approvedAt = $submittedAt
                        ? $submittedAt->copy()->addDays(rand(1, 5))
                        : Carbon::now()->subDays(rand(1, 10));
                    $approvedBy = $admin ? $admin->id : null;
                    $approvalNotes =
                        "Mohon maaf, berkas yang diupload kurang lengkap. Silakan upload ulang dengan dokumen yang sesuai.";
                }

                // Create student registration
                StudentRegistration::create([
                    "user_id" => $user->id,
                    "tanggal_lahir" => $tanggalLahir,
                    "tempat_lahir" => $tempatLahir,
                    "jenis_kelamin" => $gender,
                    "nama_orang_tua" => $namaOrangTua,
                    "phone_orang_tua" => $phoneOrangTua,
                    "alamat_orang_tua" => $alamatOrangTua,
                    "ktp_orang_tua_path" => $ktpPath,
                    "ijazah_path" => $ijazahPath,
                    "foto_siswa_path" => $fotoPath,
                    "bukti_pembayaran_path" => $buktiPembayaranPath,
                    "registration_status" => $status,
                    "submitted_at" => $submittedAt,
                    "approved_at" => $approvedAt,
                    "approval_notes" => $approvalNotes,
                    "approved_by" => $approvedBy,
                ]);

                $index++;
            }
        }

        $this->command->info("Student registrations seeded successfully!");
        $this->command->info(
            "Total registrations: " . StudentRegistration::count(),
        );

        // Show statistics
        $pending_documents = StudentRegistration::where(
            "registration_status",
            "pending_documents",
        )->count();
        $pending_approval = StudentRegistration::where(
            "registration_status",
            "pending_approval",
        )->count();
        $approved = StudentRegistration::where(
            "registration_status",
            "approved",
        )->count();
        $rejected = StudentRegistration::where(
            "registration_status",
            "rejected",
        )->count();

        $this->command->info("Statistics:");
        $this->command->info("  - Pending Documents: {$pending_documents}");
        $this->command->info("  - Pending Approval: {$pending_approval}");
        $this->command->info("  - Approved: {$approved}");
        $this->command->info("  - Rejected: {$rejected}");
    }

    /**
     * Generate parent name based on student name and gender
     */
    private function generateParentName(
        string $studentName,
        string $gender,
    ): string {
        $nameParts = explode(" ", $studentName);
        $lastName = end($nameParts);

        $fatherFirstNames = [
            "Agus",
            "Bambang",
            "Dedi",
            "Hadi",
            "Iwan",
            "Joko",
            "Rudi",
            "Sugeng",
            "Tono",
            "Wawan",
        ];
        $motherFirstNames = [
            "Ani",
            "Dewi",
            "Endah",
            "Fitri",
            "Indah",
            "Lestari",
            "Nur",
            "Ratna",
            "Sari",
            "Tri",
        ];

        // 70% use father's name, 30% use mother's name
        if (rand(1, 100) <= 70) {
            return $fatherFirstNames[array_rand($fatherFirstNames)] .
                " " .
                $lastName;
        } else {
            return $motherFirstNames[array_rand($motherFirstNames)] .
                " " .
                $lastName;
        }
    }

    /**
     * Generate random address
     */
    private function generateAddress(): string
    {
        $streets = [
            "Jl. Merdeka",
            "Jl. Sudirman",
            "Jl. Thamrin",
            "Jl. Gatot Subroto",
            "Jl. Ahmad Yani",
            "Jl. Diponegoro",
            "Jl. Imam Bonjol",
            "Jl. Veteran",
            "Jl. Pahlawan",
            "Jl. Pemuda",
        ];
        $cities = [
            "Jakarta Selatan",
            "Jakarta Utara",
            "Bandung",
            "Surabaya",
            "Semarang",
            "Yogyakarta",
            "Tangerang",
            "Bekasi",
            "Depok",
            "Bogor",
        ];

        $street = $streets[array_rand($streets)];
        $number = rand(1, 200);
        $city = $cities[array_rand($cities)];
        $postalCode = rand(10000, 99999);

        return "{$street} No. {$number}, {$city} {$postalCode}";
    }
}

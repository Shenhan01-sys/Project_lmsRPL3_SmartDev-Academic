<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get completed enrollments
        $completedEnrollments = Enrollment::where('status', 'completed')
            ->with(['course', 'student'])
            ->get();

        if ($completedEnrollments->isEmpty()) {
            $this->command->warn('No completed enrollments found. Using active enrollments for demo purposes.');
            $completedEnrollments = Enrollment::where('status', 'active')
                ->with(['course', 'student'])
                ->take(10) // Take 10 active enrollments
                ->get();
        }

        // Get an admin or instructor to be the issuer
        $issuer = User::whereIn('role', ['admin', 'instructor'])->first();
        if (!$issuer) {
            $issuer = User::first();
        }

        $count = 0;

        foreach ($completedEnrollments as $enrollment) {
            // Check if certificate already exists
            if (Certificate::where('enrollment_id', $enrollment->id)->exists()) {
                continue;
            }

            // Generate random stats if not present (assuming logic for demo)
            $finalGrade = $enrollment->final_grade ?? rand(75, 98);
            $attendancePct = rand(80, 100);
            $assignmentRate = rand(90, 100);
            
            // Determine Grade Letter
            $gradeLetter = 'A';
            if ($finalGrade < 85) $gradeLetter = 'B';
            if ($finalGrade < 75) $gradeLetter = 'C';

            Certificate::create([
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'certificate_code' => 'CERT-' . date('Y') . '-' . strtoupper(Str::random(8)),
                'certificate_file_path' => 'certificates/demo-cert.pdf', // Dummy path
                'final_grade' => $finalGrade,
                'attendance_percentage' => $attendancePct,
                'assignment_completion_rate' => $assignmentRate,
                'grade_letter' => $gradeLetter,
                'issue_date' => Carbon::now()->subDays(rand(1, 60)), // Issued recently
                'expiry_date' => null, // No expiry
                'generated_by' => $issuer->id,
                'status' => 'issued',
                'verification_count' => rand(0, 5),
            ]);

            $count++;
        }

        $this->command->info("Successfully generated {$count} certificates.");
    }
}

<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $courses = Course::where("status", "active")->get();

        if ($students->isEmpty()) {
            $this->command->warn(
                "No students found. Please run StudentSeeder first.",
            );
            return;
        }

        if ($courses->isEmpty()) {
            $this->command->warn(
                "No courses found. Please run CourseSeeder first.",
            );
            return;
        }

        $statuses = ["active", "completed", "dropped"];
        $statusWeights = [
            "active" => 70, // 70% active
            "completed" => 20, // 20% completed
            "dropped" => 10, // 10% dropped
        ];

        $enrollmentCount = 0;
        $withGradeCount = 0;

        // Each student enrolls in 3-6 courses
        foreach ($students as $student) {
            $numCourses = rand(3, 6);
            $enrolledCourses = $courses->random(
                min($numCourses, $courses->count()),
            );

            foreach ($enrolledCourses as $course) {
                // Check if already enrolled
                $exists = Enrollment::where("student_id", $student->id)
                    ->where("course_id", $course->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Weighted random status
                $rand = rand(1, 100);
                if ($rand <= $statusWeights["active"]) {
                    $status = "active";
                } elseif (
                    $rand <=
                    $statusWeights["active"] + $statusWeights["completed"]
                ) {
                    $status = "completed";
                } else {
                    $status = "dropped";
                }

                // Generate progress based on status
                if ($status === "completed") {
                    $progress = 100;
                } elseif ($status === "dropped") {
                    $progress = rand(10, 60);
                } else {
                    $progress = rand(10, 90);
                }

                // Generate enrolled_at date (1-6 months ago)
                $enrolledAt = Carbon::now()
                    ->subMonths(rand(1, 6))
                    ->subDays(rand(0, 30));

                // Generate completed_at or dropped_at if applicable
                $completedAt = null;
                $droppedAt = null;

                if ($status === "completed") {
                    $completedAt = $enrolledAt->copy()->addMonths(rand(3, 5));
                } elseif ($status === "dropped") {
                    $droppedAt = $enrolledAt->copy()->addMonths(rand(1, 3));
                }

                // ✅ IMPROVED: Generate final_grade dengan distribusi realistis
                $finalGrade = $this->generateRealisticGrade($status, $progress);
                
                if ($finalGrade !== null) {
                    $withGradeCount++;
                }

                Enrollment::create([
                    "student_id" => $student->id,
                    "course_id" => $course->id,
                    "enrollment_date" => $enrolledAt,
                    "status" => $status,
                    "final_grade" => $finalGrade,
                ]);

                $enrollmentCount++;
            }
        }

        $this->command->info("\n✅ Enrollments seeded successfully!");
        $this->command->info("   Total enrollments: {$enrollmentCount}");
        $this->command->info("   With final grades: {$withGradeCount} (" . round(($withGradeCount / $enrollmentCount) * 100, 1) . "%)");
        $this->showGradeDistribution();
    }

    /**
     * Generate realistic final grade based on status and progress
     * Menggunakan distribusi normal untuk nilai yang lebih realistis
     */
    private function generateRealisticGrade(?string $status, int $progress): ?float
    {
        // Completed: 100% dapat nilai
        if ($status === 'completed') {
            return $this->normalDistribution(80, 10); // Mean: 80, StdDev: 10
        }

        // Active: 60% sudah dapat nilai (mid-semester atau ada UTS)
        if ($status === 'active') {
            if (rand(1, 100) <= 60) {
                // Nilai mid-semester biasanya lebih rendah sedikit
                return $this->normalDistribution(75, 12);
            }
            return null; // Belum ada nilai
        }

        // Dropped: 30% ada nilai (drop setelah UTS)
        if ($status === 'dropped') {
            if (rand(1, 100) <= 30) {
                // Nilai yang drop biasanya lebih rendah
                return $this->normalDistribution(65, 15);
            }
            return null;
        }

        return null;
    }

    /**
     * Generate nilai dengan distribusi normal (realistic grade distribution)
     * Menggunakan Box-Muller transform
     */
    private function normalDistribution(float $mean, float $stdDev): float
    {
        static $spare = null;
        
        if ($spare !== null) {
            $retval = $spare * $stdDev + $mean;
            $spare = null;
        } else {
            $u = mt_rand() / mt_getrandmax();
            $v = mt_rand() / mt_getrandmax();
            
            $mag = $stdDev * sqrt(-2.0 * log($u));
            $spare = $mag * cos(2.0 * pi() * $v);
            $retval = $mag * sin(2.0 * pi() * $v) + $mean;
        }
        
        // Clamp nilai antara 0-100
        return round(max(0, min(100, $retval)), 2);
    }

    /**
     * Show grade distribution statistics
     */
    private function showGradeDistribution(): void
    {
        $enrollments = Enrollment::whereNotNull('final_grade')->get();
        
        if ($enrollments->isEmpty()) {
            return;
        }

        $total = $enrollments->count();
        $excellent = $enrollments->where('final_grade', '>=', 85)->count(); // A
        $good = $enrollments->whereBetween('final_grade', [75, 84.99])->count(); // B
        $satisfactory = $enrollments->whereBetween('final_grade', [65, 74.99])->count(); // C
        $pass = $enrollments->whereBetween('final_grade', [60, 64.99])->count(); // D
        $fail = $enrollments->where('final_grade', '<', 60)->count(); // E/F

        $this->command->info("\n📊 Grade Distribution:");
        $this->command->info("   A (85-100): {$excellent} (" . round(($excellent / $total) * 100, 1) . "%)");
        $this->command->info("   B (75-84):  {$good} (" . round(($good / $total) * 100, 1) . "%)");
        $this->command->info("   C (65-74):  {$satisfactory} (" . round(($satisfactory / $total) * 100, 1) . "%)");
        $this->command->info("   D (60-64):  {$pass} (" . round(($pass / $total) * 100, 1) . "%)");
        $this->command->info("   E (<60):    {$fail} (" . round(($fail / $total) * 100, 1) . "%)");
        
        $avgGrade = $enrollments->avg('final_grade');
        $this->command->info("\n   📈 Average Grade: " . round($avgGrade, 2));
    }
}
<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn(
                "No courses found. Please run CourseSeeder first.",
            );
            return;
        }

        $assignmentTitles = [
            "Quiz Mingguan",
            "Tugas Essay",
            "Project Akhir",
            "Presentasi Kelompok",
            "Latihan Soal",
            "Kuis Pemahaman",
            "Mini Project",
            "Tugas Analisis",
            "Praktikum",
            "Review Materi",
        ];

        foreach ($courses as $course) {
            // Each course gets 3-5 assignments
            $numAssignments = rand(3, 5);

            for ($i = 1; $i <= $numAssignments; $i++) {
                // Get random title
                $title =
                    $assignmentTitles[array_rand($assignmentTitles)] . " " . $i;

                // Generate due_date (1-8 weeks from now)
                $dueDate = Carbon::now()
                    ->addWeeks(rand(1, 8))
                    ->addDays(rand(0, 6));

                // Some assignments are already past due date
                if (rand(1, 100) <= 30) {
                    // 30% chance past due date
                    $dueDate = Carbon::now()->subWeeks(rand(1, 4));
                }

                // Max score
                $maxScore = [50, 75, 100, 150][array_rand([50, 75, 100, 150])];

                // Generate instructions
                $instructions =
                    "Kerjakan {$title} dengan teliti sesuai dengan materi yang telah dipelajari. " .
                    "Pastikan mengumpulkan tepat waktu. Nilai maksimal: {$maxScore} poin.";

                // Status - 90% published, 10% draft
                $status = rand(1, 100) <= 90 ? "published" : "draft";

                Assignment::create([
                    "course_id" => $course->id,
                    "title" => $title,
                    "description" => $instructions,
                    "due_date" => $dueDate,
                    "max_score" => $maxScore,
                    "status" => $status,
                ]);
            }
        }

        $this->command->info("Assignments seeded successfully!");
        $this->command->info("Total assignments: " . Assignment::count());
    }
}

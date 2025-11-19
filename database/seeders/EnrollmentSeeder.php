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

                Enrollment::create([
                    "student_id" => $student->id,
                    "course_id" => $course->id,
                    "enrollment_date" => $enrolledAt,
                    "status" => $status,
                    "final_grade" =>
                        $status === "completed" ? rand(70, 100) : null,
                ]);
            }
        }

        $this->command->info("Enrollments seeded successfully!");
        $this->command->info("Total enrollments: " . Enrollment::count());
    }
}

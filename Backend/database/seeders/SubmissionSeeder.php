<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = Assignment::all();

        if ($assignments->isEmpty()) {
            $this->command->warn(
                "No assignments found. Please run AssignmentSeeder first.",
            );
            return;
        }

        foreach ($assignments as $assignment) {
            // Get all enrollments for this course
            $enrollments = Enrollment::where(
                "course_id",
                $assignment->course_id,
            )->get();

            if ($enrollments->isEmpty()) {
                continue;
            }

            // Not all students submit assignments (70-90% submission rate)
            $submissionRate = rand(70, 90);
            $numSubmissions = (int) ceil(
                $enrollments->count() * ($submissionRate / 100),
            );

            $selectedEnrollments = $enrollments->random(
                min($numSubmissions, $enrollments->count()),
            );

            foreach ($selectedEnrollments as $enrollment) {
                // Check if submission already exists
                $exists = Submission::where("assignment_id", $assignment->id)
                    ->where("enrollment_id", $enrollment->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Generate file path (80% have file submission)
                $filePath = null;
                if (rand(1, 100) <= 80) {
                    $extensions = ["pdf", "docx", "zip", "pptx"];
                    $extension = $extensions[array_rand($extensions)];
                    $filePath = "submissions/" . uniqid() . "." . $extension;
                }

                // Generate grade (60% are graded)
                $grade = null;
                $feedback = null;

                if (rand(1, 100) <= 60) {
                    // Score distribution
                    $rand = rand(1, 100);
                    if ($rand <= 20) {
                        // Excellent (90-100)
                        $grade = rand(90, 100);
                    } elseif ($rand <= 50) {
                        // Good (80-89)
                        $grade = rand(80, 89);
                    } elseif ($rand <= 80) {
                        // Average (70-79)
                        $grade = rand(70, 79);
                    } elseif ($rand <= 95) {
                        // Below Average (60-69)
                        $grade = rand(60, 69);
                    } else {
                        // Poor (50-59)
                        $grade = rand(50, 59);
                    }

                    // Generate feedback based on grade
                    $feedback = $this->generateFeedback($grade);
                }

                Submission::create([
                    "assignment_id" => $assignment->id,
                    "enrollment_id" => $enrollment->id,
                    "file_path" => $filePath,
                    "grade" => $grade,
                    "feedback" => $feedback,
                ]);
            }
        }

        $this->command->info("Submissions seeded successfully!");
        $this->command->info("Total submissions: " . Submission::count());

        // Show statistics
        $total = Submission::count();
        $graded = Submission::whereNotNull("grade")->count();
        $ungraded = Submission::whereNull("grade")->count();

        $this->command->info("Statistics:");
        $this->command->info("  - Total: {$total}");
        $this->command->info("  - Graded: {$graded}");
        $this->command->info("  - Ungraded: {$ungraded}");
    }

    /**
     * Generate feedback based on grade
     */
    private function generateFeedback(int $grade): string
    {
        if ($grade >= 90) {
            $feedbacks = [
                "Excellent work! Pemahaman materi sangat baik. Pertahankan kualitas ini.",
                "Outstanding! Semua aspek dikerjakan dengan sempurna. Keep up the good work!",
                "Sangat bagus! Penjelasan detail dan lengkap. Terus tingkatkan.",
                "Perfect! Jawaban sangat komprehensif dan terstruktur dengan baik.",
            ];
        } elseif ($grade >= 80) {
            $feedbacks = [
                "Good job! Pemahaman materi baik, ada beberapa hal minor yang bisa diperbaiki.",
                "Very good! Pekerjaan solid dengan sedikit area untuk improvement.",
                "Bagus! Hampir sempurna, perhatikan detail di beberapa bagian.",
                "Well done! Kualitas kerja baik, terus pertahankan konsistensi.",
            ];
        } elseif ($grade >= 70) {
            $feedbacks = [
                "Cukup baik. Pemahaman konsep sudah ada, perlu lebih detail di beberapa bagian.",
                "Average work. Sudah memahami materi dasar, tingkatkan analisis lebih mendalam.",
                "Lumayan. Ada potensi untuk lebih baik, pelajari lagi materi yang kurang.",
                "Fair. Konsep dasar sudah dipahami, perlu pendalaman lebih lanjut.",
            ];
        } elseif ($grade >= 60) {
            $feedbacks = [
                "Perlu improvement. Beberapa konsep penting masih kurang tepat. Pelajari lebih lanjut.",
                "Below average. Masih ada gap pemahaman, diskusikan dengan instruktur.",
                "Kurang. Perlu review materi lebih intensif. Jangan ragu bertanya.",
                "Needs work. Fokus pada pemahaman konsep fundamental terlebih dahulu.",
            ];
        } else {
            $feedbacks = [
                "Perlu banyak perbaikan. Segera konsultasi untuk memahami materi dengan lebih baik.",
                "Poor. Sepertinya ada kesulitan dalam memahami materi. Mari diskusikan bersama.",
                "Sangat kurang. Perlu bimbingan tambahan. Silakan temui instruktur.",
                "Needs significant improvement. Jangan berkecil hati, mari perbaiki bersama.",
            ];
        }

        return $feedbacks[array_rand($feedbacks)];
    }
}

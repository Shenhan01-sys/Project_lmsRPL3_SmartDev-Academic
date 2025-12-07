<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua assignments yang published
        $assignments = Assignment::where('status', 'published')->get();

        if ($assignments->isEmpty()) {
            $this->command->warn('No published assignments found. Please run AssignmentSeeder first.');
            return;
        }

        $submissionCount = 0;
        $totalPossible = 0;

        foreach ($assignments as $assignment) {
            // Ambil semua enrollments untuk course ini
            $enrollments = Enrollment::where('course_id', $assignment->course_id)
                ->whereIn('status', ['active', 'completed'])
                ->get();

            if ($enrollments->isEmpty()) {
                continue;
            }

            $this->command->info("Creating submissions for assignment: {$assignment->title}");

            foreach ($enrollments as $enrollment) {
                $totalPossible++;

                // Probabilitas submit berdasarkan status enrollment:
                // - Completed: 95% submit
                // - Active: 70% submit
                $submitChance = $enrollment->status === 'completed' ? 95 : 70;

                if (rand(1, 100) > $submitChance) {
                    // Skip submission (student belum submit)
                    continue;
                }

                // Check if submission already exists
                $exists = Submission::where('enrollment_id', $enrollment->id)
                    ->where('assignment_id', $assignment->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Generate submission data
                $submissionData = $this->generateSubmissionData($assignment, $enrollment);

                try {
                    Submission::create([
                        'enrollment_id' => $enrollment->id,
                        'assignment_id' => $assignment->id,
                        'file_path' => $submissionData['file_path'],
                        'submitted_at' => $submissionData['submitted_at'],
                        'grade' => $submissionData['grade'],
                        'feedback' => $submissionData['feedback'],
                        'status' => $submissionData['status'],
                        'graded_at' => $submissionData['graded_at'],
                    ]);

                    $submissionCount++;
                } catch (\Exception $e) {
                    $this->command->warn("Error creating submission: {$e->getMessage()}");
                }
            }
        }

        $this->command->info("\n✅ Submission seeding completed!");
        $this->command->info("   Possible submissions: {$totalPossible}");
        $this->command->info("   Created submissions: {$submissionCount}");
        $this->command->info("   Completion rate: " . round(($submissionCount / max($totalPossible, 1)) * 100, 1) . "%");
        
        $this->showStatistics();
    }

    /**
     * Generate realistic submission data
     */
    private function generateSubmissionData(Assignment $assignment, Enrollment $enrollment): array
    {
        $dueDate = Carbon::parse($assignment->due_date);
        $now = Carbon::now();

        // Generate submitted_at (antara assignment created dan now)
        $assignmentCreated = Carbon::parse($assignment->created_at);
        $submittedAt = $this->generateSubmissionDate($assignmentCreated, $dueDate, $now);

        // Determine status
        $isLate = $submittedAt->isAfter($dueDate);
        $status = $isLate ? 'late' : 'submitted';

        // 80% submissions sudah digrading
        $isGraded = rand(1, 100) <= 80;

        // Generate grade jika sudah digrading
        $grade = null;
        $gradedAt = null;
        $feedback = null;

        if ($isGraded) {
            $status = 'graded';
            
            // Grade based on submission timing and student performance
            $grade = $this->generateGrade($isLate, $enrollment);
            
            // Graded 1-7 hari setelah submission
            $gradedAt = $submittedAt->copy()->addDays(rand(1, 7));
            
            // 50% chance ada feedback
            if (rand(1, 100) <= 50) {
                $feedback = $this->generateFeedback($grade);
            }
        }

        // Generate dummy file path
        $fileExtensions = ['pdf', 'docx', 'zip', 'jpg', 'png'];
        $extension = $fileExtensions[array_rand($fileExtensions)];
        $filePath = "submissions/{$assignment->id}/student_{$enrollment->student_id}_" . time() . ".{$extension}";

        return [
            'file_path' => $filePath,
            'submitted_at' => $submittedAt,
            'grade' => $grade,
            'feedback' => $feedback,
            'status' => $status,
            'graded_at' => $gradedAt,
        ];
    }

    /**
     * Generate realistic submission date
     */
    private function generateSubmissionDate(Carbon $assignmentCreated, Carbon $dueDate, Carbon $now): Carbon
    {
        // 70% submit sebelum deadline
        // 20% submit tepat waktu (hari H deadline)
        // 10% submit terlambat

        $rand = rand(1, 100);

        if ($rand <= 70) {
            // Submit sebelum deadline (1 hari - 1 minggu sebelum)
            $daysBeforeDue = rand(1, 7);
            $submittedAt = $dueDate->copy()->subDays($daysBeforeDue);
            
            // Pastikan tidak lebih awal dari assignment created
            if ($submittedAt->lessThan($assignmentCreated)) {
                $submittedAt = $assignmentCreated->copy()->addDays(rand(1, 3));
            }
            
            return $submittedAt;
        } elseif ($rand <= 90) {
            // Submit tepat di hari deadline
            return $dueDate->copy()->setTime(rand(8, 22), rand(0, 59));
        } else {
            // Submit terlambat (1-3 hari setelah deadline)
            return $dueDate->copy()->addDays(rand(1, 3))->setTime(rand(8, 22), rand(0, 59));
        }
    }

    /**
     * Generate realistic grade based on factors
     */
    private function generateGrade(bool $isLate, Enrollment $enrollment): float
    {
        // Base grade dari final_grade enrollment (jika ada)
        $baseGrade = $enrollment->final_grade ?? 75;

        // Add randomness (-10 to +10)
        $variance = rand(-10, 10);
        $grade = $baseGrade + $variance;

        // Late submission penalty (-5 to -15 points)
        if ($isLate) {
            $penalty = rand(5, 15);
            $grade -= $penalty;
        }

        // Clamp between 0-100
        return round(max(0, min(100, $grade)), 2);
    }

    /**
     * Generate feedback based on grade
     */
    private function generateFeedback(float $grade): string
    {
        if ($grade >= 90) {
            $feedbacks = [
                'Excellent work! Very well done.',
                'Outstanding submission. Keep up the great work!',
                'Perfect! Your understanding is exceptional.',
                'Fantastic job! This is exactly what was expected.',
            ];
        } elseif ($grade >= 80) {
            $feedbacks = [
                'Good job! Well executed overall.',
                'Nice work. A few minor improvements needed.',
                'Well done! Good understanding of the material.',
                'Great effort. Keep it up!',
            ];
        } elseif ($grade >= 70) {
            $feedbacks = [
                'Good effort. Some areas need improvement.',
                'Satisfactory work. Review the feedback carefully.',
                'Decent attempt. Focus on the key concepts.',
                'Acceptable work. Can be improved further.',
            ];
        } elseif ($grade >= 60) {
            $feedbacks = [
                'Needs improvement. Please review the material.',
                'Below expectations. Come see me during office hours.',
                'Requires more effort. Study the examples provided.',
                'Not quite there yet. Keep working on it.',
            ];
        } else {
            $feedbacks = [
                'This needs significant improvement. Please redo.',
                'Did not meet the requirements. See me ASAP.',
                'Incomplete submission. Review assignment instructions.',
                'Far below expectations. Remedial work needed.',
            ];
        }

        return $feedbacks[array_rand($feedbacks)];
    }

    /**
     * Show submission statistics
     */
    private function showStatistics(): void
    {
        $total = Submission::count();
        
        if ($total === 0) {
            return;
        }

        $graded = Submission::where('status', 'graded')->count();
        $submitted = Submission::where('status', 'submitted')->count();
        $late = Submission::where('status', 'late')->count();

        $this->command->info("\n📊 Submission Statistics:");
        $this->command->info("   Graded: {$graded} (" . round(($graded / $total) * 100, 1) . "%)");
        $this->command->info("   Submitted (not graded): {$submitted} (" . round(($submitted / $total) * 100, 1) . "%)");
        $this->command->info("   Late: {$late} (" . round(($late / $total) * 100, 1) . "%)");

        // Average grade
        $avgGrade = Submission::whereNotNull('grade')->avg('grade');
        if ($avgGrade) {
            $this->command->info("\n   📈 Average Grade: " . round($avgGrade, 2));
        }
    }
}
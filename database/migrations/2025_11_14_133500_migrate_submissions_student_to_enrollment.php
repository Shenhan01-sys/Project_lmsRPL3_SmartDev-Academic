<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate enrollment_id based on student_id + course_id from assignment
        DB::statement("
            UPDATE submissions s
            JOIN assignments a ON s.assignment_id = a.id
            JOIN enrollments e ON e.student_id = s.student_id AND e.course_id = a.course_id
            SET s.enrollment_id = e.id
            WHERE s.enrollment_id IS NULL
        ");

        // Populate submission_date from created_at if null
        DB::statement("
            UPDATE submissions
            SET submission_date = created_at
            WHERE submission_date IS NULL
        ");

        // Check for orphaned records (submissions without valid enrollment)
        $orphaned = DB::table('submissions')
            ->whereNull('enrollment_id')
            ->count();

        if ($orphaned > 0) {
            throw new \Exception("Found {$orphaned} submissions without valid enrollment. Please fix manually before proceeding.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set enrollment_id back to null (data restoration would require manual intervention)
        DB::table('submissions')->update(['enrollment_id' => null, 'submission_date' => null]);
    }
};

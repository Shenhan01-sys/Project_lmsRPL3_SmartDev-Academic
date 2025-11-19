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
        // Populate enrollment_id based on student_id + course_id from grade_component
        DB::statement("
            UPDATE grades g
            JOIN grade_components gc ON g.grade_component_id = gc.id
            JOIN enrollments e ON e.student_id = g.student_id AND e.course_id = gc.course_id
            SET g.enrollment_id = e.id
            WHERE g.enrollment_id IS NULL
        ");

        // Check for orphaned records (grades without valid enrollment)
        $orphaned = DB::table('grades')
            ->whereNull('enrollment_id')
            ->count();

        if ($orphaned > 0) {
            throw new \Exception("Found {$orphaned} grades without valid enrollment. Please fix manually before proceeding.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set enrollment_id back to null (data restoration would require manual intervention)
        DB::table('grades')->update(['enrollment_id' => null]);
    }
};

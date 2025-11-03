<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if user_id column exists
        if (Schema::hasColumn('enrollments', 'user_id')) {
            // Drop old data first (since we're restructuring)
            DB::table('enrollments')->truncate();
            
            // Use raw SQL to drop column without FK constraint check
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement('ALTER TABLE enrollments DROP COLUMN user_id;');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        // Add student_id if it doesn't exist
        if (!Schema::hasColumn('enrollments', 'student_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                // Add new student_id column with FK
                $table->foreignId('student_id')->after('id')->constrained('students')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Drop new FK and column
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
            
            // Restore old user_id column with FK
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });
    }
};

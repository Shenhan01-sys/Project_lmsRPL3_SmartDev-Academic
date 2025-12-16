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
        if (Schema::hasColumn('submissions', 'user_id')) {
            // Drop old data first (since we're restructuring)
            DB::table('submissions')->truncate();
            
            Schema::table('submissions', function (Blueprint $table) {
                // Drop old FK and column
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
        
        // Add student_id if it doesn't exist
        if (!Schema::hasColumn('submissions', 'student_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                // Add new student_id column with FK
                $table->foreignId('student_id')->after('assignment_id')->constrained('students')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Drop new FK and column
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
            
            // Restore old user_id column with FK
            $table->foreignId('user_id')->after('assignment_id')->constrained('users')->onDelete('cascade');
        });
    }
};

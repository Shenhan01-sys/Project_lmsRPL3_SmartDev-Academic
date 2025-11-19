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
        Schema::table('courses', function (Blueprint $table) {
            // Drop old FK constraint first
            $table->dropForeign(['instructor_id']);
            
            // Make instructor_id nullable
            $table->unsignedBigInteger('instructor_id')->nullable()->change();
        });
        
        // Now set all instructor_id to null
        DB::table('courses')->update(['instructor_id' => null]);
        
        Schema::table('courses', function (Blueprint $table) {
            // Add new FK to reference instructors table
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop new FK
            $table->dropForeign(['instructor_id']);
            
            // Restore old FK to users table
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

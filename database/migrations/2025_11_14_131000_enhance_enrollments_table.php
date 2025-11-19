<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->date('enrollment_date')->nullable()->after('course_id');
            $table->enum('status', ['active', 'completed', 'dropped', 'failed'])->default('active')->after('enrollment_date');
            $table->decimal('final_grade', 5, 2)->nullable()->after('status');

            // Indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['enrollment_date', 'status', 'final_grade']);
        });
    }
};

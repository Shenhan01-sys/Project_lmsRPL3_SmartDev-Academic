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
        Schema::table('submissions', function (Blueprint $table) {
            // Add new enrollment_id column (nullable first for data migration)
            $table->foreignId('enrollment_id')->nullable()->after('id');

            // Add new columns for enhanced functionality
            $table->dateTime('submission_date')->nullable()->after('assignment_id');
            $table->enum('status', ['submitted', 'graded', 'late', 'resubmit'])->default('submitted')->after('feedback');
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->timestamp('graded_at')->nullable()->after('graded_by');

            // Add indexes
            $table->index('enrollment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropIndex(['enrollment_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['enrollment_id', 'submission_date', 'status', 'graded_by', 'graded_at']);
        });
    }
};

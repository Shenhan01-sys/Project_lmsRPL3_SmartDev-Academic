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
            // Add status field with enum values
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned'])
                  ->default('draft')
                  ->after('enrollment_id');

            // Add submitted_at timestamp to track when student actually submits
            $table->timestamp('submitted_at')->nullable()->after('status');

            // Add is_late boolean to track late submissions
            $table->boolean('is_late')->default(false)->after('submitted_at');

            // Add late_days to track how many days late
            $table->integer('late_days')->default(0)->after('is_late');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['status', 'submitted_at', 'is_late', 'late_days']);
        });
    }
};

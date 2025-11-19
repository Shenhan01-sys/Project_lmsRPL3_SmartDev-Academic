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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('certificate_code')->unique();
            $table->string('certificate_file_path');
            $table->decimal('final_grade', 5, 2);
            $table->decimal('attendance_percentage', 5, 2);
            $table->decimal('assignment_completion_rate', 5, 2);
            $table->char('grade_letter', 2)->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['issued', 'revoked', 'expired'])->default('issued');
            $table->text('revocation_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->integer('verification_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('certificate_code');
            $table->index('enrollment_id');
            $table->index('course_id');
            $table->index('status');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

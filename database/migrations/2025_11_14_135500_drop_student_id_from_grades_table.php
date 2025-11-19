<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Drop the composite index
        DB::statement(
            "ALTER TABLE grades DROP INDEX grades_student_id_grade_component_id_index",
        );

        // Step 2: Drop student_id foreign key constraint
        DB::statement(
            "ALTER TABLE grades DROP FOREIGN KEY grades_student_id_foreign",
        );

        // Step 3: Drop student_id column
        DB::statement("ALTER TABLE grades DROP COLUMN student_id");

        Schema::table("grades", function (Blueprint $table) {
            // Make enrollment_id NOT NULL
            $table
                ->unsignedBigInteger("enrollment_id")
                ->nullable(false)
                ->change();

            // Add foreign key constraint for enrollment_id
            $table
                ->foreign("enrollment_id")
                ->references("id")
                ->on("enrollments")
                ->onDelete("cascade");

            // Add new unique constraint
            $table->unique(
                ["enrollment_id", "grade_component_id"],
                "unique_grade_enrollment",
            );

            // Add new index
            $table->index(["enrollment_id", "grade_component_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("grades", function (Blueprint $table) {
            // Drop new unique constraint and index
            $table->dropUnique("unique_grade_enrollment");
            $table->dropIndex(["enrollment_id", "grade_component_id"]);

            // Drop foreign key for enrollment_id
            $table->dropForeign(["enrollment_id"]);

            // Make enrollment_id nullable
            $table->unsignedBigInteger("enrollment_id")->nullable()->change();
        });

        // Add back student_id column with constraints
        DB::statement(
            "ALTER TABLE grades ADD COLUMN student_id BIGINT UNSIGNED NOT NULL AFTER enrollment_id",
        );
        DB::statement(
            "ALTER TABLE grades ADD CONSTRAINT grades_student_id_foreign FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE",
        );
        DB::statement(
            "ALTER TABLE grades ADD UNIQUE KEY grades_student_id_grade_component_id_unique (student_id, grade_component_id)",
        );
        DB::statement(
            "ALTER TABLE grades ADD INDEX grades_student_id_grade_component_id_index (student_id, grade_component_id)",
        );
    }
};

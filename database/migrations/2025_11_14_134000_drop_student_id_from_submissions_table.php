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
        // Step 1: Drop assignment FK that depends on the composite unique index
        DB::statement(
            "ALTER TABLE submissions DROP FOREIGN KEY submissions_assignment_id_foreign",
        );

        // Step 2: Drop the composite unique constraint
        DB::statement(
            "ALTER TABLE submissions DROP INDEX submissions_assignment_id_student_id_unique",
        );

        // Step 3: Drop student_id foreign key constraint
        DB::statement(
            "ALTER TABLE submissions DROP FOREIGN KEY submissions_student_id_foreign",
        );

        // Step 4: Drop student_id column
        DB::statement("ALTER TABLE submissions DROP COLUMN student_id");

        // Step 5: Recreate assignment FK
        DB::statement(
            "ALTER TABLE submissions ADD CONSTRAINT submissions_assignment_id_foreign FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE",
        );

        Schema::table("submissions", function (Blueprint $table) {
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
                ["enrollment_id", "assignment_id"],
                "unique_submission_enrollment",
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("submissions", function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique("unique_submission_enrollment");

            // Drop foreign key for enrollment_id
            $table->dropForeign(["enrollment_id"]);

            // Make enrollment_id nullable
            $table->unsignedBigInteger("enrollment_id")->nullable()->change();
        });

        // Add back student_id column with unique constraint
        DB::statement(
            "ALTER TABLE submissions ADD COLUMN student_id BIGINT UNSIGNED NOT NULL AFTER enrollment_id",
        );
        DB::statement(
            "ALTER TABLE submissions ADD CONSTRAINT submissions_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE",
        );
        DB::statement(
            "ALTER TABLE submissions ADD UNIQUE KEY submissions_assignment_id_student_id_unique (assignment_id, student_id)",
        );
    }
};

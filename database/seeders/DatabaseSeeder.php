<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info("🌱 Starting database seeding...");
        $this->command->newLine();

        // 1. Create Admin User (if not exists)
        $this->command->info("1️⃣  Checking admin user...");
        $admin = User::where("email", "admin@example.com")->first();
        if (!$admin) {
            User::create([
                "name" => "Admin User",
                "email" => "admin@example.com",
                "password" => Hash::make("password"),
                "role" => "admin",
                "level" => null,
            ]);
            $this->command->info("✅ Admin user created successfully!");
        } else {
            $this->command->info("✅ Admin user already exists!");
        }
        $this->command->newLine();

        // 2. Seed Parents
        $this->command->info("2️⃣  Seeding parents...");
        $this->call(ParentSeeder::class);
        $this->command->newLine();

        // 3. Seed Instructors
        $this->command->info("3️⃣  Seeding instructors...");
        $this->call(InstructorSeeder::class);
        $this->command->newLine();

        // 4. Seed Students
        $this->command->info("4️⃣  Seeding students...");
        $this->call(StudentSeeder::class);
        $this->command->newLine();

        // 5. Seed Student Registrations (Calon Siswa)
        $this->command->info(
            "5️⃣  Seeding student registrations (calon siswa)...",
        );
        $this->call(StudentRegistrationSeeder::class);
        $this->command->newLine();

        // 6. Seed Courses
        $this->command->info("6️⃣  Seeding courses...");
        $this->call(CourseSeeder::class);
        $this->command->newLine();

        // 7. Seed Course Modules
        $this->command->info("7️⃣  Seeding course modules...");
        $this->call(CourseModuleSeeder::class);
        $this->command->newLine();

        // 8. Seed Materials
        $this->command->info("8️⃣  Seeding materials...");
        $this->call(MaterialSeeder::class);
        $this->command->newLine();

        // 9. Seed Enrollments
        $this->command->info("9️⃣  Seeding enrollments...");
        $this->call(EnrollmentSeeder::class);
        $this->command->newLine();

        // 10. Seed Assignments
        $this->command->info("🔟 Seeding assignments...");
        $this->call(AssignmentSeeder::class);
        $this->command->newLine();

        // 11. Seed Submissions
        $this->command->info("1️⃣1️⃣  Seeding submissions...");
        $this->call(SubmissionSeeder::class);
        $this->command->newLine();

        // 12. Seed Grade Components
        $this->command->info("1️⃣2️⃣  Seeding grade components...");
        $this->call(GradeComponentSeeder::class);
        $this->command->newLine();

        // 13. Seed Grades
        $this->command->info("1️⃣3️⃣  Seeding grades...");
        $this->call(GradeSeeder::class);
        $this->command->newLine();

        // 14. Seed Attendance Sessions and Records
        $this->command->info(
            "1️⃣4️⃣  Seeding attendance sessions and records...",
        );
        $this->call(AttendanceSeeder::class);
        $this->command->newLine();

        // 15. Seed Announcements
        $this->command->info("1️⃣5️⃣  Seeding announcements...");
        $this->call(AnnouncementSeeder::class);
        $this->command->newLine();

        // 16. Seed Notifications
        $this->command->info("1️⃣6️⃣  Seeding notifications...");
        $this->call(NotificationSeeder::class);
        $this->command->newLine();

        // Note: Certificates are NOT seeded as per user request

        $this->command->newLine();
        $this->command->info("🎉 ========================================");
        $this->command->info("🎉 DATABASE SEEDING COMPLETED SUCCESSFULLY!");
        $this->command->info("🎉 ========================================");
        $this->command->newLine();

        $this->showSummary();
    }

    /**
     * Show seeding summary
     */
    private function showSummary(): void
    {
        $this->command->info("📊 Seeding Summary:");
        $this->command->table(
            ["Table", "Count"],
            [
                ["Users", \App\Models\User::count()],
                ["Parents", \App\Models\Parents::count()],
                ["Instructors", \App\Models\Instructor::count()],
                ["Students", \App\Models\Student::count()],
                [
                    "Student Registrations",
                    \App\Models\StudentRegistration::count(),
                ],
                ["Courses", \App\Models\Course::count()],
                ["Course Modules", \App\Models\CourseModule::count()],
                ["Materials", \App\Models\Material::count()],
                ["Enrollments", \App\Models\Enrollment::count()],
                ["Assignments", \App\Models\Assignment::count()],
                ["Submissions", \App\Models\Submission::count()],
                ["Grade Components", \App\Models\GradeComponent::count()],
                ["Grades", \App\Models\Grade::count()],
                ["Attendance Sessions", \App\Models\AttendanceSession::count()],
                ["Attendance Records", \App\Models\AttendanceRecord::count()],
                ["Announcements", \App\Models\Announcement::count()],
                ["Notifications", \App\Models\Notification::count()],
            ],
        );

        $this->command->newLine();
        $this->command->info("🔑 Login Credentials:");
        $this->command->info("   Admin:");
        $this->command->info("   📧 Email: admin@example.com");
        $this->command->info("   🔒 Password: password");
        $this->command->newLine();
        $this->command->info("   Instructors & Students:");
        $this->command->info("   📧 Email: (check users table)");
        $this->command->info("   🔒 Password: password123");
        $this->command->newLine();
        $this->command->info("   Calon Siswa:");
        $this->command->info(
            "   📧 Email: ahmad.fauzi@example.com (and others)",
        );
        $this->command->info("   🔒 Password: password123");
        $this->command->newLine();
    }
}

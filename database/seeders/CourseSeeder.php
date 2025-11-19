<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructors = Instructor::all();

        if ($instructors->isEmpty()) {
            $this->command->warn(
                "No instructors found. Please run InstructorSeeder first.",
            );
            return;
        }

        $courses = [
            // Matematika
            [
                "course_name" => "Matematika Dasar",
                "course_code" => "MTK101",
                "description" =>
                    "Mempelajari konsep dasar matematika termasuk aljabar, geometri, dan statistik dasar",
                "credit_hours" => 3,
                "category" => "Matematika",
                "level" => "SMP",
                "thumbnail" => "courses/matematika-dasar.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Matematika Lanjutan",
                "course_code" => "MTK201",
                "description" =>
                    "Matematika tingkat lanjut meliputi kalkulus, trigonometri, dan analisis data",
                "credit_hours" => 4,
                "category" => "Matematika",
                "level" => "SMA",
                "thumbnail" => "courses/matematika-lanjutan.jpg",
                "status" => "published",
            ],
            // Fisika
            [
                "course_name" => "Fisika Dasar",
                "course_code" => "FIS101",
                "description" =>
                    "Pengenalan konsep dasar fisika: gerak, gaya, energi, dan gelombang",
                "credit_hours" => 3,
                "category" => "Sains",
                "level" => "SMP",
                "thumbnail" => "courses/fisika-dasar.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Fisika Modern",
                "course_code" => "FIS201",
                "description" =>
                    "Fisika modern meliputi mekanika kuantum, relativitas, dan fisika nuklir",
                "credit_hours" => 4,
                "category" => "Sains",
                "level" => "SMA",
                "thumbnail" => "courses/fisika-modern.jpg",
                "status" => "published",
            ],
            // Kimia
            [
                "course_name" => "Kimia Dasar",
                "course_code" => "KIM101",
                "description" =>
                    "Mempelajari struktur atom, ikatan kimia, reaksi kimia, dan stoikiometri",
                "credit_hours" => 3,
                "category" => "Sains",
                "level" => "SMP",
                "thumbnail" => "courses/kimia-dasar.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Kimia Organik",
                "course_code" => "KIM201",
                "description" =>
                    "Studi tentang senyawa karbon, hidrokarbon, dan aplikasi kimia organik",
                "credit_hours" => 4,
                "category" => "Sains",
                "level" => "SMA",
                "thumbnail" => "courses/kimia-organik.jpg",
                "status" => "published",
            ],
            // Biologi
            [
                "course_name" => "Biologi Umum",
                "course_code" => "BIO101",
                "description" =>
                    "Pengenalan konsep biologi: sel, genetika, ekologi, dan evolusi",
                "credit_hours" => 3,
                "category" => "Sains",
                "level" => "SMP",
                "thumbnail" => "courses/biologi-umum.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Biologi Molekuler",
                "course_code" => "BIO201",
                "description" =>
                    "Studi mendalam tentang DNA, RNA, protein, dan proses seluler",
                "credit_hours" => 4,
                "category" => "Sains",
                "level" => "SMA",
                "thumbnail" => "courses/biologi-molekuler.jpg",
                "status" => "published",
            ],
            // Bahasa Indonesia
            [
                "course_name" => "Bahasa Indonesia",
                "course_code" => "IND101",
                "description" =>
                    "Pembelajaran tata bahasa, sastra, dan keterampilan menulis bahasa Indonesia",
                "credit_hours" => 2,
                "category" => "Bahasa",
                "level" => "SMP",
                "thumbnail" => "courses/bahasa-indonesia.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Sastra Indonesia",
                "course_code" => "IND201",
                "description" =>
                    "Kajian mendalam tentang sastra Indonesia klasik dan modern",
                "credit_hours" => 3,
                "category" => "Bahasa",
                "level" => "SMA",
                "thumbnail" => "courses/sastra-indonesia.jpg",
                "status" => "published",
            ],
            // Bahasa Inggris
            [
                "course_name" => "English Foundation",
                "course_code" => "ENG101",
                "description" =>
                    "Basic English grammar, vocabulary, and conversation skills",
                "credit_hours" => 3,
                "category" => "Bahasa",
                "level" => "SMP",
                "thumbnail" => "courses/english-foundation.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Advanced English",
                "course_code" => "ENG201",
                "description" =>
                    "Advanced English including academic writing and literature",
                "credit_hours" => 3,
                "category" => "Bahasa",
                "level" => "SMA",
                "thumbnail" => "courses/advanced-english.jpg",
                "status" => "published",
            ],
            // Sejarah
            [
                "course_name" => "Sejarah Indonesia",
                "course_code" => "SEJ101",
                "description" =>
                    "Sejarah Indonesia dari masa kerajaan hingga kemerdekaan",
                "credit_hours" => 2,
                "category" => "Sosial",
                "level" => "SMP",
                "thumbnail" => "courses/sejarah-indonesia.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Sejarah Dunia",
                "course_code" => "SEJ201",
                "description" =>
                    "Sejarah peradaban dunia dari masa kuno hingga kontemporer",
                "credit_hours" => 3,
                "category" => "Sosial",
                "level" => "SMA",
                "thumbnail" => "courses/sejarah-dunia.jpg",
                "status" => "published",
            ],
            // Geografi
            [
                "course_name" => "Geografi",
                "course_code" => "GEO101",
                "description" =>
                    "Studi tentang bumi, lingkungan, dan interaksi manusia dengan alam",
                "credit_hours" => 2,
                "category" => "Sosial",
                "level" => "SMP",
                "thumbnail" => "courses/geografi.jpg",
                "status" => "published",
            ],
            // Ekonomi
            [
                "course_name" => "Ekonomi Dasar",
                "course_code" => "EKO101",
                "description" =>
                    "Konsep dasar ekonomi: penawaran, permintaan, pasar, dan sistem ekonomi",
                "credit_hours" => 2,
                "category" => "Sosial",
                "level" => "SMP",
                "thumbnail" => "courses/ekonomi-dasar.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Ekonomi Makro",
                "course_code" => "EKO201",
                "description" =>
                    "Studi tentang ekonomi makro: inflasi, pengangguran, dan kebijakan fiskal",
                "credit_hours" => 3,
                "category" => "Sosial",
                "level" => "SMA",
                "thumbnail" => "courses/ekonomi-makro.jpg",
                "status" => "published",
            ],
            // Teknologi
            [
                "course_name" => "Pemrograman Dasar",
                "course_code" => "TIK101",
                "description" =>
                    "Pengenalan pemrograman dengan Python dan konsep algoritma",
                "credit_hours" => 3,
                "category" => "Teknologi",
                "level" => "SMP",
                "thumbnail" => "courses/pemrograman-dasar.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Web Development",
                "course_code" => "TIK201",
                "description" =>
                    "Belajar membuat website dengan HTML, CSS, JavaScript, dan framework modern",
                "credit_hours" => 4,
                "category" => "Teknologi",
                "level" => "SMA",
                "thumbnail" => "courses/web-development.jpg",
                "status" => "published",
            ],
            // Seni
            [
                "course_name" => "Seni Rupa",
                "course_code" => "SEN101",
                "description" =>
                    "Eksplorasi seni rupa meliputi menggambar, melukis, dan desain",
                "credit_hours" => 2,
                "category" => "Seni",
                "level" => "SMP",
                "thumbnail" => "courses/seni-rupa.jpg",
                "status" => "published",
            ],
            [
                "course_name" => "Seni Musik",
                "course_code" => "MUS101",
                "description" =>
                    "Teori musik, bernyanyi, dan bermain alat musik",
                "credit_hours" => 2,
                "category" => "Seni",
                "level" => "SMP",
                "thumbnail" => "courses/seni-musik.jpg",
                "status" => "published",
            ],
            // Olahraga
            [
                "course_name" => "Pendidikan Jasmani",
                "course_code" => "PJK101",
                "description" =>
                    "Olahraga dan kesehatan jasmani untuk pengembangan fisik",
                "credit_hours" => 2,
                "category" => "Olahraga",
                "level" => "SMP",
                "thumbnail" => "courses/pendidikan-jasmani.jpg",
                "status" => "published",
            ],
        ];

        foreach ($courses as $courseData) {
            // Randomly assign instructor
            $instructor = $instructors->random();

            Course::create([
                "instructor_id" => $instructor->id,
                "course_name" => $courseData["course_name"],
                "course_code" => $courseData["course_code"],
                "description" => $courseData["description"],
                "credits" => $courseData["credit_hours"],
                "max_students" => rand(20, 40),
                "status" =>
                    $courseData["status"] === "published"
                        ? "active"
                        : "inactive",
            ]);
        }

        $this->command->info("Courses seeded successfully!");
    }
}

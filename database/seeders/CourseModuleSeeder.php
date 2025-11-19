<?php

namespace Database\Seeders;

use App\Models\CourseModule;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn(
                "No courses found. Please run CourseSeeder first.",
            );
            return;
        }

        $moduleTemplates = [
            "Matematika" => [
                [
                    "title" => "Pengenalan dan Konsep Dasar",
                    "description" =>
                        "Memahami konsep fundamental dan prinsip dasar",
                ],
                [
                    "title" => "Operasi dan Perhitungan",
                    "description" =>
                        "Mempelajari berbagai operasi dan teknik perhitungan",
                ],
                [
                    "title" => "Aplikasi dan Pemecahan Masalah",
                    "description" =>
                        "Menerapkan konsep dalam pemecahan masalah nyata",
                ],
                [
                    "title" => "Latihan dan Praktik",
                    "description" => "Latihan soal dan praktik intensif",
                ],
                [
                    "title" => "Review dan Ujian",
                    "description" => "Review materi dan persiapan ujian",
                ],
            ],
            "Sains" => [
                [
                    "title" => "Teori Fundamental",
                    "description" => "Memahami teori dan konsep fundamental",
                ],
                [
                    "title" => "Eksperimen dan Observasi",
                    "description" => "Praktik eksperimen dan observasi ilmiah",
                ],
                [
                    "title" => "Analisis Data",
                    "description" => "Menganalisis dan menginterpretasi data",
                ],
                [
                    "title" => "Studi Kasus",
                    "description" =>
                        "Mempelajari kasus-kasus nyata dan aplikasi",
                ],
                [
                    "title" => "Proyek Akhir",
                    "description" => "Proyek penelitian sederhana",
                ],
            ],
            "Bahasa" => [
                [
                    "title" => "Struktur dan Tata Bahasa",
                    "description" => "Mempelajari struktur dan kaidah bahasa",
                ],
                [
                    "title" => "Kosakata dan Ekspresi",
                    "description" => "Memperkaya kosakata dan ungkapan",
                ],
                [
                    "title" => "Membaca dan Memahami",
                    "description" =>
                        "Meningkatkan kemampuan membaca dan pemahaman",
                ],
                [
                    "title" => "Menulis dan Berbicara",
                    "description" => "Praktik menulis dan berbicara",
                ],
                [
                    "title" => "Proyek Kreatif",
                    "description" => "Proyek menulis atau presentasi kreatif",
                ],
            ],
            "Sosial" => [
                [
                    "title" => "Konsep dan Teori",
                    "description" => "Memahami konsep dan teori dasar",
                ],
                [
                    "title" => "Analisis Konteks",
                    "description" =>
                        "Menganalisis konteks sosial, ekonomi, atau sejarah",
                ],
                [
                    "title" => "Studi Komparatif",
                    "description" => "Membandingkan berbagai perspektif",
                ],
                [
                    "title" => "Diskusi dan Debat",
                    "description" => "Diskusi aktif dan debat konstruktif",
                ],
                [
                    "title" => "Presentasi Proyek",
                    "description" => "Presentasi hasil penelitian atau proyek",
                ],
            ],
            "Teknologi" => [
                [
                    "title" => "Pengenalan Tools dan Lingkungan",
                    "description" =>
                        "Setup dan pengenalan tools yang digunakan",
                ],
                [
                    "title" => "Konsep Fundamental",
                    "description" => "Memahami konsep dan prinsip dasar",
                ],
                [
                    "title" => "Praktik Coding",
                    "description" => "Praktik coding dan implementasi",
                ],
                [
                    "title" => "Project Development",
                    "description" => "Pengembangan project sederhana",
                ],
                [
                    "title" => "Final Project",
                    "description" => "Project akhir komprehensif",
                ],
            ],
            "Seni" => [
                [
                    "title" => "Dasar-dasar Seni",
                    "description" => "Memahami elemen dan prinsip seni",
                ],
                [
                    "title" => "Teknik dan Metode",
                    "description" => "Mempelajari berbagai teknik dan metode",
                ],
                [
                    "title" => "Eksplorasi Kreatif",
                    "description" => "Eksplorasi dan eksperimen kreatif",
                ],
                [
                    "title" => "Portfolio Development",
                    "description" => "Mengembangkan portfolio karya",
                ],
                [
                    "title" => "Exhibition",
                    "description" => "Pameran dan presentasi karya",
                ],
            ],
            "Olahraga" => [
                [
                    "title" => "Pemanasan dan Conditioning",
                    "description" => "Teknik pemanasan dan conditioning fisik",
                ],
                [
                    "title" => "Teknik Dasar",
                    "description" => "Mempelajari teknik dasar olahraga",
                ],
                [
                    "title" => "Strategi dan Taktik",
                    "description" => "Memahami strategi dan taktik permainan",
                ],
                [
                    "title" => "Team Building",
                    "description" => "Kerjasama tim dan koordinasi",
                ],
                [
                    "title" => "Kompetisi",
                    "description" => "Simulasi kompetisi dan pertandingan",
                ],
            ],
        ];

        $defaultModules = [
            [
                "title" => "Modul Pengenalan",
                "description" => "Pengenalan materi dan overview course",
            ],
            [
                "title" => "Modul Pembelajaran",
                "description" => "Pembelajaran inti materi",
            ],
            [
                "title" => "Modul Praktik",
                "description" => "Praktik dan latihan",
            ],
            ["title" => "Modul Review", "description" => "Review dan evaluasi"],
        ];

        foreach ($courses as $course) {
            // Get appropriate module template based on category
            $modules = $moduleTemplates[$course->category] ?? $defaultModules;

            $order = 1;
            foreach ($modules as $moduleData) {
                CourseModule::create([
                    "course_id" => $course->id,
                    "title" => $moduleData["title"],
                    "module_order" => $order,
                ]);
                $order++;
            }
        }

        $this->command->info("Course modules seeded successfully!");
        $this->command->info("Total modules: " . CourseModule::count());
    }
}

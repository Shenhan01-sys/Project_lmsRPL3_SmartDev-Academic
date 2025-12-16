<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = CourseModule::all();

        if ($modules->isEmpty()) {
            $this->command->warn(
                "No course modules found. Please run CourseModuleSeeder first.",
            );
            return;
        }

        $materialTypes = ["file", "video", "link"];

        $materialTitles = [
            "file" => [
                "Materi Pembelajaran",
                "Handout",
                "Catatan Kuliah",
                "Ringkasan Materi",
                "Referensi Tambahan",
                "Panduan Praktikum",
                "Lembar Kerja",
            ],
            "video" => [
                "Video Pembelajaran",
                "Tutorial Video",
                "Video Demonstrasi",
                "Rekaman Kuliah",
                "Video Penjelasan",
            ],
            "link" => [
                "Sumber Referensi Online",
                "Link Artikel",
                "Resource Tambahan",
                "Website Pembelajaran",
                "Link Tutorial",
            ],
        ];

        foreach ($modules as $module) {
            // Each module gets 2-4 materials
            $numMaterials = rand(2, 4);

            for ($i = 1; $i <= $numMaterials; $i++) {
                // Randomly select material type
                $type = $materialTypes[array_rand($materialTypes)];

                // Get random title for this type
                $titleOptions = $materialTitles[$type];
                $title = $titleOptions[array_rand($titleOptions)] . " " . $i;

                // Generate content path based on type
                $contentPath = null;

                switch ($type) {
                    case "file":
                        $contentPath =
                            "materials/documents/" . uniqid() . ".pdf";
                        break;
                    case "video":
                        $contentPath = "materials/videos/" . uniqid() . ".mp4";
                        break;
                    case "link":
                        $contentPath =
                            "https://example.com/resource/" . uniqid();
                        break;
                }

                Material::create([
                    "module_id" => $module->id,
                    "title" => $title,
                    "material_type" => $type,
                    "content_path" => $contentPath,
                    "file_size" => in_array($type, ["file", "video"])
                        ? rand(100000, 5000000)
                        : null, // 100KB - 5MB
                ]);
            }
        }

        $this->command->info("Materials seeded successfully!");
        $this->command->info("Total materials: " . Material::count());
    }
}

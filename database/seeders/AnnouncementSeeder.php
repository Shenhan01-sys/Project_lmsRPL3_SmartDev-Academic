<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $admins = User::where('role', 'admin')->get();
        $instructors = User::where('role', 'instructor')->get();

        if ($admins->isEmpty()) {
            $this->command->warn('No admins found. Announcements require admin users.');
            return;
        }

        // Global Announcements (from admin)
        $globalAnnouncements = [
            [
                'title' => 'Selamat Datang di SmartDev LMS',
                'content' => 'Selamat datang di platform pembelajaran SmartDev LMS! Kami sangat senang Anda bergabung dengan kami. Platform ini dirancang untuk memberikan pengalaman belajar yang optimal. Jangan ragu untuk menghubungi support jika ada pertanyaan.',
                'announcement_type' => 'global',
                'priority' => 'normal',
                'status' => 'published',
                'published_at' => Carbon::now()->subMonths(3),
                'pinned' => true,
            ],
            [
                'title' => 'Pemeliharaan Sistem Terjadwal',
                'content' => 'Sistem akan menjalani pemeliharaan rutin pada hari Minggu, 2 Februari 2025 pukul 01:00 - 05:00 WIB. Selama periode ini, akses ke platform akan terbatas. Mohon maaf atas ketidaknyamanannya.',
                'announcement_type' => 'global',
                'priority' => 'high',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(7),
                'expires_at' => Carbon::now()->addDays(7),
                'pinned' => true,
            ],
            [
                'title' => 'Fitur Baru: Video Conference',
                'content' => 'Kami dengan bangga memperkenalkan fitur video conference terintegrasi! Sekarang Anda dapat mengikuti kelas online langsung dari platform ini. Lihat panduan penggunaan di bagian Help Center.',
                'announcement_type' => 'global',
                'priority' => 'high',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(14),
                'pinned' => false,
            ],
            [
                'title' => 'Libur Nasional - Hari Kemerdekaan',
                'content' => 'Dalam rangka memperingati Hari Kemerdekaan Republik Indonesia, tidak ada aktivitas pembelajaran pada tanggal 17 Agustus 2025. Selamat Hari Kemerdekaan!',
                'announcement_type' => 'global',
                'priority' => 'normal',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(30),
                'pinned' => false,
            ],
            [
                'title' => 'Panduan Penggunaan Platform',
                'content' => 'Silakan download panduan lengkap penggunaan platform SmartDev LMS di bagian Resources. Panduan mencakup cara mengakses materi, submit tugas, dan menggunakan fitur-fitur lainnya.',
                'announcement_type' => 'global',
                'priority' => 'normal',
                'status' => 'published',
                'published_at' => Carbon::now()->subMonths(2),
                'pinned' => false,
            ],
            [
                'title' => 'Survey Kepuasan Pengguna',
                'content' => 'Kami mengundang Anda untuk mengisi survey kepuasan pengguna. Feedback Anda sangat berharga untuk pengembangan platform. Link survey: https://survey.smartdevlms.com',
                'announcement_type' => 'global',
                'priority' => 'normal',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addDays(30),
                'pinned' => false,
            ],
            [
                'title' => 'Update Kebijakan Privasi',
                'content' => 'Kami telah memperbarui kebijakan privasi kami. Silakan baca kebijakan privasi terbaru di halaman Privacy Policy. Perubahan berlaku efektif mulai 1 Februari 2025.',
                'announcement_type' => 'global',
                'priority' => 'high',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(20),
                'pinned' => false,
            ],
            [
                'title' => 'URGENT: Keamanan Akun',
                'content' => 'PENTING! Jangan pernah membagikan password Anda kepada siapapun. Tim SmartDev LMS tidak akan pernah meminta password Anda. Gunakan password yang kuat dan unik. Aktifkan two-factor authentication untuk keamanan tambahan.',
                'announcement_type' => 'global',
                'priority' => 'urgent',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(3),
                'pinned' => true,
            ],
        ];

        foreach ($globalAnnouncements as $announcementData) {
            Announcement::create([
                'created_by' => $admins->random()->id,
                'course_id' => null,
                'title' => $announcementData['title'],
                'content' => $announcementData['content'],
                'announcement_type' => $announcementData['announcement_type'],
                'priority' => $announcementData['priority'],
                'status' => $announcementData['status'],
                'published_at' => $announcementData['published_at'],
                'expires_at' => $announcementData['expires_at'] ?? null,
                'view_count' => rand(50, 500),
                'pinned' => $announcementData['pinned'],
            ]);
        }

        // Course-specific Announcements (from instructors)
        if ($courses->isNotEmpty() && $instructors->isNotEmpty()) {
            $courseAnnouncementTemplates = [
                [
                    'title' => 'Materi Baru Tersedia',
                    'content' => 'Materi pembelajaran baru telah diupload. Silakan pelajari materi tersebut sebelum pertemuan berikutnya.',
                    'priority' => 'normal',
                ],
                [
                    'title' => 'Pengingat: Deadline Tugas',
                    'content' => 'Mengingatkan bahwa deadline pengumpulan tugas tinggal 3 hari lagi. Pastikan Anda mengumpulkan tepat waktu.',
                    'priority' => 'high',
                ],
                [
                    'title' => 'Perubahan Jadwal Kelas',
                    'content' => 'Terdapat perubahan jadwal kelas minggu ini. Kelas akan dimulai 30 menit lebih awal. Mohon perhatiannya.',
                    'priority' => 'high',
                ],
                [
                    'title' => 'Quiz Akan Dilaksanakan',
                    'content' => 'Quiz untuk bab ini akan dilaksanakan pada pertemuan berikutnya. Silakan pelajari materi dengan baik.',
                    'priority' => 'normal',
                ],
                [
                    'title' => 'Nilai Tugas Sudah Keluar',
                    'content' => 'Nilai untuk tugas terakhir sudah dapat dilihat. Silakan cek di bagian Grades. Jika ada pertanyaan, silakan hubungi instruktur.',
                    'priority' => 'normal',
                ],
                [
                    'title' => 'Sesi Tanya Jawab',
                    'content' => 'Akan ada sesi tanya jawab khusus untuk persiapan ujian. Sesi akan dilaksanakan secara online. Jangan lewatkan!',
                    'priority' => 'normal',
                ],
                [
                    'title' => 'Tugas Kelompok',
                    'content' => 'Untuk tugas kelompok berikutnya, silakan bentuk kelompok maksimal 4 orang. Submit nama anggota kelompok minggu depan.',
                    'priority' => 'high',
                ],
                [
                    'title' => 'Informasi Ujian Tengah Semester',
                    'content' => 'UTS akan dilaksanakan 2 minggu lagi. Format ujian: 50% pilihan ganda, 50% essay. Durasi: 120 menit. Persiapkan diri dengan baik.',
                    'priority' => 'urgent',
                ],
                [
                    'title' => 'Pembahasan Soal',
                    'content' => 'Pembahasan soal tugas kemarin akan dilakukan di pertemuan berikutnya. Silakan siapkan pertanyaan jika ada yang kurang jelas.',
                    'priority' => 'normal',
                ],
                [
                    'title' => 'Batas Akhir Revisi',
                    'content' => 'Bagi yang ingin revisi tugas, batas akhir pengumpulan revisi adalah hari Jumat ini. Tidak ada perpanjangan waktu.',
                    'priority' => 'high',
                ],
            ];

            foreach ($courses as $course) {
                // Each course gets 3-6 announcements
                $numAnnouncements = rand(3, 6);
                $selectedTemplates = collect($courseAnnouncementTemplates)->random(min($numAnnouncements, count($courseAnnouncementTemplates)));

                // Get course instructor or random instructor
                $instructor = $course->instructor->user ?? $instructors->random();

                foreach ($selectedTemplates as $template) {
                    // Some announcements are scheduled for future
                    $isFuture = rand(1, 100) <= 20; // 20% future announcements

                    if ($isFuture) {
                        $publishedAt = Carbon::now()->addDays(rand(1, 14));
                        $status = 'draft';
                    } else {
                        $publishedAt = Carbon::now()->subDays(rand(1, 30));
                        $status = 'published';
                    }

                    // Some announcements expire
                    $expiresAt = null;
                    if (rand(1, 100) <= 30) { // 30% have expiration
                        $expiresAt = $publishedAt->copy()->addDays(rand(7, 30));
                    }

                    Announcement::create([
                        'created_by' => $instructor->id,
                        'course_id' => $course->id,
                        'title' => $template['title'],
                        'content' => $template['content'],
                        'announcement_type' => 'course',
                        'priority' => $template['priority'],
                        'status' => $status,
                        'published_at' => $publishedAt,
                        'expires_at' => $expiresAt,
                        'view_count' => $status === 'published' ? rand(10, 100) : 0,
                        'pinned' => rand(1, 100) <= 15, // 15% pinned
                    ]);
                }
            }
        }

        $this->command->info('Announcements seeded successfully!');
        $this->command->info('Total announcements: ' . Announcement::count());

        // Show statistics
        $global = Announcement::where('announcement_type', 'global')->count();
        $course = Announcement::where('announcement_type', 'course')->count();
        $published = Announcement::where('status', 'published')->count();
        $draft = Announcement::where('status', 'draft')->count();
        $pinned = Announcement::where('pinned', true)->count();

        $this->command->info("Statistics:");
        $this->command->info("  - Global: {$global}");
        $this->command->info("  - Course-specific: {$course}");
        $this->command->info("  - Published: {$published}");
        $this->command->info("  - Draft: {$draft}");
        $this->command->info("  - Pinned: {$pinned}");
    }
}

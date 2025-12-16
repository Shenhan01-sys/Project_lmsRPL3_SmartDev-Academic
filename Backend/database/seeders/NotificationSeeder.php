<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['student', 'instructor', 'parent'])->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run user seeders first.');
            return;
        }

        $courses = Course::all();
        $assignments = Assignment::all();
        $announcements = Announcement::where('status', 'published')->get();

        $notificationTypes = [
            'assignment_created',
            'assignment_graded',
            'assignment_reminder',
            'announcement',
            'course_enrollment',
            'grade_updated',
            'attendance_marked',
            'submission_received',
            'course_update',
            'system',
        ];

        // Notification templates based on type
        $templates = [
            'assignment_created' => [
                'title' => 'Tugas Baru',
                'message' => 'Tugas baru "{title}" telah dibuat untuk mata kuliah {course}. Deadline: {deadline}.',
                'type' => 'assignment',
            ],
            'assignment_graded' => [
                'title' => 'Tugas Dinilai',
                'message' => 'Tugas "{title}" Anda telah dinilai. Nilai: {score}. Lihat feedback dari instruktur.',
                'type' => 'grade',
            ],
            'assignment_reminder' => [
                'title' => 'Pengingat Deadline Tugas',
                'message' => 'Tugas "{title}" akan berakhir dalam {days} hari. Segera kumpulkan tugas Anda!',
                'type' => 'reminder',
            ],
            'announcement' => [
                'title' => 'Pengumuman Baru',
                'message' => '{announcement_title}',
                'type' => 'announcement',
            ],
            'course_enrollment' => [
                'title' => 'Pendaftaran Kursus Berhasil',
                'message' => 'Anda telah berhasil mendaftar ke mata kuliah {course}. Selamat belajar!',
                'type' => 'enrollment',
            ],
            'grade_updated' => [
                'title' => 'Nilai Diperbarui',
                'message' => 'Nilai Anda untuk {course} telah diperbarui. Cek nilai terbaru di halaman Grades.',
                'type' => 'grade',
            ],
            'attendance_marked' => [
                'title' => 'Absensi Tercatat',
                'message' => 'Absensi Anda untuk {course} - {session} telah tercatat sebagai {status}.',
                'type' => 'attendance',
            ],
            'submission_received' => [
                'title' => 'Pengumpulan Tugas Diterima',
                'message' => 'Pengumpulan tugas "{title}" telah diterima. Tunggu penilaian dari instruktur.',
                'type' => 'submission',
            ],
            'course_update' => [
                'title' => 'Pembaruan Kursus',
                'message' => 'Materi baru telah ditambahkan ke {course}. Silakan cek materi terbaru.',
                'type' => 'course',
            ],
            'system' => [
                'title' => 'Notifikasi Sistem',
                'message' => 'Selamat datang di SmartDev LMS! Jelajahi fitur-fitur yang tersedia.',
                'type' => 'system',
            ],
        ];

        foreach ($users as $user) {
            // Each user gets 5-15 notifications
            $numNotifications = rand(5, 15);

            for ($i = 0; $i < $numNotifications; $i++) {
                // Select random notification type
                $notifType = $notificationTypes[array_rand($notificationTypes)];
                $template = $templates[$notifType];

                // Customize message based on type
                $title = $template['title'];
                $message = $template['message'];
                $type = $template['type'];
                $relatedId = null;
                $relatedType = null;

                // Replace placeholders with actual data
                if (in_array($notifType, ['assignment_created', 'assignment_graded', 'assignment_reminder', 'submission_received'])) {
                    if ($assignments->isNotEmpty()) {
                        $assignment = $assignments->random();
                        $message = str_replace('{title}', $assignment->assignment_title, $message);
                        $message = str_replace('{course}', $assignment->course->course_name ?? 'Kursus', $message);
                        $message = str_replace('{deadline}', $assignment->deadline->format('d M Y'), $message);
                        $message = str_replace('{score}', rand(70, 100), $message);
                        $message = str_replace('{days}', rand(1, 3), $message);
                        $relatedId = $assignment->id;
                        $relatedType = 'assignment';
                    }
                } elseif ($notifType === 'announcement') {
                    if ($announcements->isNotEmpty()) {
                        $announcement = $announcements->random();
                        $message = $announcement->title . ' - ' . substr($announcement->content, 0, 100) . '...';
                        $relatedId = $announcement->id;
                        $relatedType = 'announcement';
                    }
                } elseif (in_array($notifType, ['course_enrollment', 'grade_updated', 'course_update', 'attendance_marked'])) {
                    if ($courses->isNotEmpty()) {
                        $course = $courses->random();
                        $message = str_replace('{course}', $course->course_name, $message);
                        $message = str_replace('{session}', 'Pertemuan ' . rand(1, 10), $message);
                        $message = str_replace('{status}', 'Hadir', $message);
                        $relatedId = $course->id;
                        $relatedType = 'course';
                    }
                }

                // Generate created_at timestamp (1-30 days ago)
                $createdAt = Carbon::now()->subDays(rand(1, 30))->subHours(rand(0, 23));

                // Some notifications are read, some are not
                $isRead = rand(1, 100) <= 60; // 60% are read
                $readAt = $isRead ? $createdAt->copy()->addHours(rand(1, 48)) : null;

                // Priority based on notification type
                $priority = match($notifType) {
                    'assignment_reminder', 'assignment_graded' => 'high',
                    'announcement', 'grade_updated' => 'medium',
                    default => 'low',
                };

                Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'priority' => $priority,
                    'is_read' => $isRead,
                    'read_at' => $readAt,
                    'related_id' => $relatedId,
                    'related_type' => $relatedType,
                    'action_url' => $this->generateActionUrl($type, $relatedId),
                    'created_at' => $createdAt,
                    'updated_at' => $readAt ?? $createdAt,
                ]);
            }
        }

        $this->command->info('Notifications seeded successfully!');
        $this->command->info('Total notifications: ' . Notification::count());

        // Show statistics
        $total = Notification::count();
        $read = Notification::where('is_read', true)->count();
        $unread = Notification::where('is_read', false)->count();
        $highPriority = Notification::where('priority', 'high')->count();

        $this->command->info("Statistics:");
        $this->command->info("  - Total: {$total}");
        $this->command->info("  - Read: {$read}");
        $this->command->info("  - Unread: {$unread}");
        $this->command->info("  - High Priority: {$highPriority}");
    }

    /**
     * Generate action URL based on notification type
     */
    private function generateActionUrl(string $type, ?int $relatedId): ?string
    {
        if (!$relatedId) {
            return null;
        }

        return match($type) {
            'assignment' => "/assignments/{$relatedId}",
            'announcement' => "/announcements/{$relatedId}",
            'course' => "/courses/{$relatedId}",
            'grade' => "/grades",
            'attendance' => "/attendance",
            'enrollment' => "/my-courses",
            'submission' => "/submissions/{$relatedId}",
            default => null,
        };
    }
}

<?php

namespace Database\Seeders;

use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::where('status', 'published')->get();

        if ($courses->isEmpty()) {
            $this->command->warn('No courses found. Please run CourseSeeder first.');
            return;
        }

        $sessionNames = [
            'Pertemuan 1 - Pengenalan',
            'Pertemuan 2 - Materi Inti',
            'Pertemuan 3 - Praktik',
            'Pertemuan 4 - Diskusi',
            'Pertemuan 5 - Review',
            'Pertemuan 6 - Quiz',
            'Pertemuan 7 - Project Work',
            'Pertemuan 8 - Presentasi',
            'Pertemuan 9 - Evaluasi',
            'Pertemuan 10 - Final Review',
        ];

        foreach ($courses as $course) {
            // Each course has 6-10 attendance sessions
            $numSessions = rand(6, 10);

            for ($i = 0; $i < $numSessions; $i++) {
                // Some sessions are in the past (closed), some are current/future (open)
                $isPast = rand(1, 100) <= 70; // 70% are past sessions

                if ($isPast) {
                    // Past sessions: 1-8 weeks ago
                    $startTime = Carbon::now()->subWeeks(rand(1, 8))->subDays(rand(0, 6));
                    $status = 'closed';
                } else {
                    // Future/current sessions: 1-4 weeks ahead
                    $startTime = Carbon::now()->addWeeks(rand(0, 4))->addDays(rand(0, 6));
                    $status = rand(1, 100) <= 50 ? 'open' : 'closed';
                }

                $endTime = $startTime->copy()->addHours(rand(1, 3));
                $deadline = $endTime->copy()->addMinutes(15); // 15 min after end

                $session = AttendanceSession::create([
                    'course_id' => $course->id,
                    'session_name' => $sessionNames[$i] ?? "Pertemuan " . ($i + 1),
                    'status' => $status,
                    'deadline' => $deadline,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

                // Create attendance records for enrolled students
                if ($isPast || $status === 'closed') {
                    $this->createAttendanceRecords($session);
                }
            }
        }

        $this->command->info('Attendance sessions and records seeded successfully!');
        $this->command->info('Total sessions: ' . AttendanceSession::count());
        $this->command->info('Total records: ' . AttendanceRecord::count());
    }

    /**
     * Create attendance records for a session
     */
    private function createAttendanceRecords(AttendanceSession $session): void
    {
        $enrollments = Enrollment::where('course_id', $session->course_id)
            ->where('status', 'active')
            ->get();

        if ($enrollments->isEmpty()) {
            return;
        }

        $statuses = ['present', 'absent', 'sick', 'permission'];
        $statusWeights = [
            'present' => 75,      // 75% present
            'absent' => 15,       // 15% absent
            'sick' => 6,          // 6% sick
            'permission' => 4,    // 4% permission
        ];

        foreach ($enrollments as $enrollment) {
            // Weighted random status
            $rand = rand(1, 100);
            if ($rand <= $statusWeights['present']) {
                $status = 'present';
            } elseif ($rand <= $statusWeights['present'] + $statusWeights['absent']) {
                $status = 'absent';
            } elseif ($rand <= $statusWeights['present'] + $statusWeights['absent'] + $statusWeights['sick']) {
                $status = 'sick';
            } else {
                $status = 'permission';
            }

            // Check-in time (for present students)
            $checkedInAt = null;
            if ($status === 'present') {
                // Check-in between start time and end time
                $minutesAfterStart = rand(0, 30); // Check-in within 30 minutes of start
                $checkedInAt = $session->start_time->copy()->addMinutes($minutesAfterStart);
            }

            // Notes for non-present students
            $notes = null;
            if ($status !== 'present') {
                $notesOptions = [
                    'absent' => [
                        'Tidak hadir tanpa keterangan',
                        'Alpha',
                        null,
                    ],
                    'sick' => [
                        'Sakit',
                        'Sakit dengan surat dokter',
                        'Izin sakit',
                        'Tidak enak badan',
                    ],
                    'permission' => [
                        'Izin keperluan keluarga',
                        'Izin acara sekolah',
                        'Izin keperluan pribadi',
                        'Dispensasi',
                    ],
                ];

                $options = $notesOptions[$status] ?? [null];
                $notes = $options[array_rand($options)];
            }

            AttendanceRecord::create([
                'enrollment_id' => $enrollment->id,
                'attendance_session_id' => $session->id,
                'status' => $status,
                'checked_in_at' => $checkedInAt,
                'notes' => $notes,
            ]);
        }
    }
}

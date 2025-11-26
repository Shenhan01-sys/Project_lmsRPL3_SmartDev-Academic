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
        $courses = Course::all();

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
            // Simulate a full semester (14-16 weeks)
            $numSessions = rand(14, 16);
            
            // Start date: 4 months ago to cover "past" sessions
            $semesterStart = Carbon::now()->subMonths(4)->startOfWeek();

            for ($i = 0; $i < $numSessions; $i++) {
                // Weekly sessions
                $sessionDate = $semesterStart->copy()->addWeeks($i)->addDays(rand(0, 4)); // Mon-Fri
                
                // Determine status based on date
                if ($sessionDate->isPast()) {
                    $status = 'closed';
                } else {
                    $status = 'open';
                    // If it's too far in future, maybe 'scheduled' but enum only has open/closed usually? 
                    // Checking migration: enum('open','closed') in testingData.txt
                    // So future sessions might be 'closed' (not yet open) or 'open' if we want to allow early check-in?
                    // Usually future sessions are 'closed' until the day of.
                    if ($sessionDate->diffInDays(Carbon::now()) > 1) {
                         $status = 'closed'; 
                    } else {
                         $status = 'open';
                    }
                }

                $startTime = $sessionDate->setTime(rand(8, 15), 0); // 08:00 - 15:00
                $endTime = $startTime->copy()->addHours(2);
                $deadline = $endTime->copy()->addMinutes(30);

                $session = AttendanceSession::create([
                    'course_id' => $course->id,
                    'session_name' => $sessionNames[$i] ?? "Pertemuan Ke-" . ($i + 1),
                    'status' => $status,
                    'deadline' => $deadline,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

                // Create records only for past/closed sessions or active open sessions
                if ($sessionDate->isPast() || $status === 'open') {
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
                $status = 'excused'; // sick -> excused
            } else {
                $status = 'excused'; // permission -> excused
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
                    'excused' => [
                        'Sakit',
                        'Sakit dengan surat dokter',
                        'Izin sakit',
                        'Izin keperluan keluarga',
                        'Izin acara sekolah',
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
                'attendance_time' => $checkedInAt ?? $session->start_time,
                'notes' => $notes,
            ]);
        }
    }
}

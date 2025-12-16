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
            'Pertemuan 10 - Ujian Tengah Semester',
            'Pertemuan 11 - Studi Kasus',
            'Pertemuan 12 - Praktikum',
            'Pertemuan 13 - Review Akhir',
            'Pertemuan 14 - Ujian Akhir Semester',
        ];

        $sessionCount = 0;
        $recordCount = 0;

        foreach ($courses as $course) {
            $this->command->info("Creating attendance sessions for course: {$course->course_name}");
            
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
                    // Future sessions
                    if ($sessionDate->diffInDays(Carbon::now()) > 1) {
                        $status = 'closed'; // Not yet open
                    } else {
                        $status = 'open'; // Today or tomorrow
                    }
                }

                $startTime = $sessionDate->copy()->setTime(rand(8, 15), 0, 0); // 08:00 - 15:00
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

                $sessionCount++;

                // Create records only for past/closed sessions or active open sessions
                if ($sessionDate->isPast() || $status === 'open') {
                    $recordCount += $this->createAttendanceRecords($session);
                }
            }
        }

        $this->command->info("\n✅ Attendance seeded successfully!");
        $this->command->info("   Total sessions: {$sessionCount}");
        $this->command->info("   Total records: {$recordCount}");
        
        // Show statistics
        $this->showStatistics();
    }

    /**
     * Create attendance records for a session
     */
    private function createAttendanceRecords(AttendanceSession $session): int
    {
        $enrollments = Enrollment::where('course_id', $session->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->get();

        if ($enrollments->isEmpty()) {
            return 0;
        }

        $recordCount = 0;
        
        // ✅ Status weights sesuai enum yang ada: present, absent, late
        $statusWeights = [
            'present' => 75,      // 75% present
            'absent' => 15,       // 15% absent
            'late' => 10,         // 10% late (terlambat)
        ];

        foreach ($enrollments as $enrollment) {
            // Weighted random status
            $rand = rand(1, 100);
            
            if ($rand <= $statusWeights['present']) {
                $attendanceStatus = 'present';
            } elseif ($rand <= $statusWeights['present'] + $statusWeights['absent']) {
                $attendanceStatus = 'absent';
            } else {
                $attendanceStatus = 'late';
            }

            $recordData = $this->generateRecordData($attendanceStatus, $session);

            try {
                AttendanceRecord::create([
                    'enrollment_id' => $enrollment->id,
                    'attendance_session_id' => $session->id,
                    'attendance_status' => $attendanceStatus, // ✅ Kolom yang benar
                    'attendance_time' => $recordData['attendance_time'],
                    'notes' => $recordData['notes'],
                ]);
                $recordCount++;
            } catch (\Exception $e) {
                $this->command->warn("Error: {$e->getMessage()}");
            }
        }

        return $recordCount;
    }

    /**
     * Generate attendance record data based on status
     */
    private function generateRecordData(string $attendanceStatus, AttendanceSession $session): array
    {
        $data = [
            'attendance_time' => $session->start_time,
            'notes' => null,
        ];

        switch ($attendanceStatus) {
            case 'present':
                // Check-in tepat waktu (0-15 menit setelah start)
                $minutesAfterStart = rand(0, 15);
                $data['attendance_time'] = $session->start_time->copy()->addMinutes($minutesAfterStart);
                
                // 10% chance ada notes
                if (rand(1, 100) <= 10) {
                    $notes = [
                        'Hadir tepat waktu',
                        'Check-in berhasil',
                        'Aktif mengikuti kelas',
                        'Partisipasi baik',
                    ];
                    $data['notes'] = $notes[array_rand($notes)];
                }
                break;

            case 'late':
                // Check-in terlambat (15-45 menit setelah start)
                $minutesLate = rand(15, 45);
                $data['attendance_time'] = $session->start_time->copy()->addMinutes($minutesLate);
                
                // 50% chance ada notes untuk late
                if (rand(1, 100) <= 50) {
                    $notes = [
                        'Terlambat karena macet',
                        'Terlambat ' . $minutesLate . ' menit',
                        'Hadir terlambat',
                    ];
                    $data['notes'] = $notes[array_rand($notes)];
                }
                break;

            case 'absent':
                // Untuk absent, time = start time, bisa ada notes
                if (rand(1, 100) <= 30) {
                    $notes = [
                        'Tidak hadir tanpa keterangan',
                        'Alpha',
                        'Tidak ada konfirmasi',
                        'Sakit tanpa surat',
                    ];
                    $data['notes'] = $notes[array_rand($notes)];
                }
                break;
        }

        return $data;
    }

    /**
     * Show attendance statistics
     */
    private function showStatistics(): void
    {
        $total = AttendanceRecord::count();
        
        if ($total === 0) {
            $this->command->warn('   No attendance records created.');
            return;
        }

        $present = AttendanceRecord::where('attendance_status', 'present')->count();
        $absent = AttendanceRecord::where('attendance_status', 'absent')->count();
        $late = AttendanceRecord::where('attendance_status', 'late')->count();

        $this->command->info("\n📊 Attendance Statistics:");
        $this->command->info("   Present: {$present} (" . round(($present / $total) * 100, 1) . "%)");
        $this->command->info("   Absent: {$absent} (" . round(($absent / $total) * 100, 1) . "%)");
        $this->command->info("   Late: {$late} (" . round(($late / $total) * 100, 1) . "%)");
        $this->command->info("\n   ✅ Attendance percentage will be calculated as (Present + Late) / Total");
    }
}
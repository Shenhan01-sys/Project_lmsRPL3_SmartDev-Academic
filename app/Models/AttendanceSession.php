<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $table = 'attendence_sessions';

    protected $fillable = [
        'course_id',
        'session_name',
        'status',
        'deadline',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the course that owns the attendance session.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the attendance records for the session.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Scope a query to only include open sessions.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include closed sessions.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope a query to only include active sessions (not yet deadline).
     */
    public function scopeActive($query)
    {
        return $query->where('deadline', '>', now())
            ->where('status', 'open');
    }

    /**
     * Scope a query to only include expired sessions (past deadline).
     */
    public function scopeExpired($query)
    {
        return $query->where('deadline', '<=', now());
    }

    /**
     * Check if the session is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'open' && $this->deadline->isFuture();
    }

    /**
     * Check if the session has expired.
     */
    public function hasExpired(): bool
    {
        return $this->deadline->isPast();
    }

    /**
     * Open the attendance session.
     */
    public function open(): void
    {
        $this->update(['status' => 'open']);
    }

    /**
     * Close the attendance session.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Auto-mark absent for students who haven't checked in.
     */
    public function autoMarkAbsent(): int
    {
        if (!$this->hasExpired()) {
            return 0;
        }

        // Get all enrollments for this course
        $enrollments = $this->course->enrollments()->where('status', 'active')->get();

        $markedCount = 0;

        foreach ($enrollments as $enrollment) {
            // Check if student has attendance record for this session
            $record = AttendanceRecord::where('enrollment_id', $enrollment->id)
                ->where('attendance_session_id', $this->id)
                ->first();

            // If no record exists or status is still pending, mark as absent
            if (!$record) {
                AttendanceRecord::create([
                    'enrollment_id' => $enrollment->id,
                    'attendance_session_id' => $this->id,
                    'status' => 'absent',
                ]);
                $markedCount++;
            } elseif ($record->status === 'pending') {
                $record->update(['status' => 'absent']);
                $markedCount++;
            }
        }

        return $markedCount;
    }

    /**
     * Get attendance summary for this session.
     */
    public function getAttendanceSummary(): array
    {
        $records = $this->attendanceRecords;

        return [
            'total' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'sick' => $records->where('status', 'sick')->count(),
            'permission' => $records->where('status', 'permission')->count(),
            'pending' => $records->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get attendance percentage for this session.
     */
    public function getAttendancePercentage(): float
    {
        $summary = $this->getAttendanceSummary();

        if ($summary['total'] === 0) {
            return 0;
        }

        return round(($summary['present'] / $summary['total']) * 100, 2);
    }
}

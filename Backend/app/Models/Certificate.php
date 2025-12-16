<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'certificate_code',
        'certificate_file_path',
        'final_grade',
        'attendance_percentage',
        'assignment_completion_rate',
        'grade_letter',
        'issue_date',
        'expiry_date',
        'generated_by',
        'status',
        'revocation_reason',
        'revoked_at',
        'verification_count',
        'metadata',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
        'final_grade' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
        'assignment_completion_rate' => 'decimal:2',
        'verification_count' => 'integer',
    ];

    /**
     * Get the enrollment that owns the certificate.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the course that owns the certificate.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who generated the certificate.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the student through enrollment.
     */
    public function student()
    {
        return $this->enrollment->student;
    }

    /**
     * Scope a query to only include issued certificates.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope a query to only include revoked certificates.
     */
    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    /**
     * Scope a query to only include expired certificates.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope a query to only include valid certificates.
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'issued')
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            });
    }

    /**
     * Increment the verification count.
     */
    public function incrementVerificationCount(): void
    {
        $this->increment('verification_count');
    }

    /**
     * Revoke the certificate.
     */
    public function revoke(string $reason, int $revokedBy): void
    {
        $this->update([
            'status' => 'revoked',
            'revocation_reason' => $reason,
            'revoked_at' => now(),
        ]);

        // Add to metadata
        $metadata = $this->metadata ?? [];
        $metadata['revoked_by'] = $revokedBy;
        $metadata['revoked_at'] = now()->toDateTimeString();
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Check if certificate is valid.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if certificate is expired by date.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Get the grade letter based on final grade.
     */
    public static function calculateGradeLetter(float $grade): string
    {
        if ($grade >= 90) return 'A';
        if ($grade >= 80) return 'B';
        if ($grade >= 70) return 'C';
        if ($grade >= 60) return 'D';
        return 'F';
    }

    /**
     * Generate a unique certificate code.
     */
    public static function generateCertificateCode(string $courseCode, int $year = null): string
    {
        $year = $year ?? now()->year;
        $random = strtoupper(Str::random(8));

        return "CERT-{$year}-{$courseCode}-{$random}";
    }

    /**
     * Check eligibility for certificate generation.
     */
    public static function checkEligibility(Enrollment $enrollment): array
    {
        $errors = [];

        // Check enrollment status
        if ($enrollment->status !== 'completed') {
            $errors[] = 'Enrollment must be completed';
        }

        // Check final grade
        if (!$enrollment->final_grade || $enrollment->final_grade < 60) {
            $errors[] = 'Final grade must be at least 60';
        }

        // Calculate attendance percentage
        $attendancePercentage = static::calculateAttendancePercentage($enrollment);
        if ($attendancePercentage < 75) {
            $errors[] = 'Attendance percentage must be at least 75%';
        }

        // Calculate assignment completion rate
        $completionRate = static::calculateAssignmentCompletionRate($enrollment);
        if ($completionRate < 80) {
            $errors[] = 'Assignment completion rate must be at least 80%';
        }

        return [
            'eligible' => empty($errors),
            'errors' => $errors,
            'attendance_percentage' => $attendancePercentage,
            'assignment_completion_rate' => $completionRate,
        ];
    }

    /**
     * Calculate attendance percentage for enrollment.
     */
    protected static function calculateAttendancePercentage(Enrollment $enrollment): float
    {
        $totalSessions = $enrollment->course->attendanceSessions()->count();

        if ($totalSessions === 0) {
            return 100; // No sessions = 100%
        }

        $presentCount = $enrollment->attendanceRecords()
            ->where('status', 'present')
            ->count();

        return round(($presentCount / $totalSessions) * 100, 2);
    }

    /**
     * Calculate assignment completion rate for enrollment.
     */
    protected static function calculateAssignmentCompletionRate(Enrollment $enrollment): float
    {
        $totalAssignments = $enrollment->course->assignments()
            ->where('status', 'published')
            ->count();

        if ($totalAssignments === 0) {
            return 100; // No assignments = 100%
        }

        $submittedCount = $enrollment->submissions()->count();

        return round(($submittedCount / $totalAssignments) * 100, 2);
    }
}

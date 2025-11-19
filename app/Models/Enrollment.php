<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;
    protected $fillable = [
        "student_id",
        "course_id",
        "enrollment_date",
        "status",
        "final_grade",
    ];

    protected $casts = [
        "enrollment_date" => "date",
        "final_grade" => "decimal:2",
    ];

    /**
     * Get the student that owns the enrollment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, "student_id");
    }

    /**
     * Get the course that owns the enrollment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the submissions for the enrollment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get the grades for the enrollment.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get the attendance records for the enrollment.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the certificates for the enrollment.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Scope a query to only include active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where("status", "active");
    }

    /**
     * Scope a query to only include completed enrollments.
     */
    public function scopeCompleted($query)
    {
        return $query->where("status", "completed");
    }

    /**
     * Check if enrollment is active.
     */
    public function isActive(): bool
    {
        return $this->status === "active";
    }

    /**
     * Check if enrollment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === "completed";
    }

    /**
     * Calculate final grade based on all grade components.
     */
    public function calculateFinalGrade(): float
    {
        $gradeComponents = $this->course
            ->gradeComponents()
            ->where("is_active", true)
            ->get();

        if ($gradeComponents->isEmpty()) {
            return 0;
        }

        $totalWeight = $gradeComponents->sum("weight");
        $weightedScore = 0;

        foreach ($gradeComponents as $component) {
            $grade = $this->grades()
                ->where("grade_component_id", $component->id)
                ->first();

            if ($grade) {
                $percentage = ($grade->score / $grade->max_score) * 100;
                $weightedScore +=
                    $percentage * ($component->weight / $totalWeight);
            }
        }

        return round($weightedScore, 2);
    }

    /**
     * Update the final grade.
     */
    public function updateFinalGrade(): void
    {
        $this->update(["final_grade" => $this->calculateFinalGrade()]);
    }

    /**
     * Get attendance percentage.
     */
    public function getAttendancePercentage(): float
    {
        $totalSessions = $this->course->attendanceSessions()->count();

        if ($totalSessions === 0) {
            return 100;
        }

        $presentCount = $this->attendanceRecords()
            ->where("status", "present")
            ->count();

        return round(($presentCount / $totalSessions) * 100, 2);
    }

    /**
     * Get assignment completion rate.
     */
    public function getAssignmentCompletionRate(): float
    {
        $totalAssignments = $this->course
            ->assignments()
            ->where("status", "published")
            ->count();

        if ($totalAssignments === 0) {
            return 100;
        }

        $submittedCount = $this->submissions()->count();

        return round(($submittedCount / $totalAssignments) * 100, 2);
    }
}

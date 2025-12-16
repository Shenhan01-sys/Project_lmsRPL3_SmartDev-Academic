<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        "course_code",
        "course_name",
        "description",
        "instructor_id",
        "credits",
        "max_students",
        "status",
    ];

    protected $casts = [
        "credits" => "integer",
        "max_students" => "integer",
    ];

    /**
     * Get the instructor that owns the course.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, "instructor_id");
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the course modules for the course.
     */
    public function courseModules(): HasMany
    {
        return $this->hasMany(CourseModule::class);
    }

    /**
     * Get the assignments for the course.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the grade components for the course.
     */
    public function gradeComponents(): HasMany
    {
        return $this->hasMany(GradeComponent::class);
    }

    /**
     * Get the attendance sessions for the course.
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Get the announcements for the course.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get the certificates for the course.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Scope a query to only include active courses.
     */
    public function scopeActive($query)
    {
        return $query->where("status", "active");
    }

    /**
     * Scope a query to only include inactive courses.
     */
    public function scopeInactive($query)
    {
        return $query->where("status", "inactive");
    }

    /**
     * Check if the course is active.
     */
    public function isActive(): bool
    {
        return $this->status === "active";
    }

    /**
     * Check if the course has reached max students.
     */
    public function isFull(): bool
    {
        if (!$this->max_students) {
            return false;
        }

        return $this->enrollments()->where("status", "active")->count() >=
            $this->max_students;
    }

    /**
     * Get available slots.
     */
    public function getAvailableSlots(): ?int
    {
        if (!$this->max_students) {
            return null;
        }

        $enrolled = $this->enrollments()->where("status", "active")->count();
        return max(0, $this->max_students - $enrolled);
    }
}

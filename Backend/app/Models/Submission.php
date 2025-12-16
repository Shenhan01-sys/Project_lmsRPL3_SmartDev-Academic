<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        "assignment_id",
        "enrollment_id",
        "file_path",
        "grade",
        "feedback",
        "status",
        "submitted_at",
        "is_late",
        "late_days",
    ];

    protected $casts = [
        "submitted_at" => "datetime",
        "is_late" => "boolean",
        "grade" => "decimal:2",
    ];

    // ✅ TAMBAHKAN INI: Auto-append file_url ke JSON response
    protected $appends = ['file_url'];

    /**
     * Get full URL for file_path
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return asset('storage/' . $this->file_path);
    }
    
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Check if submission is a draft
     */
    public function isDraft(): bool
    {
        return $this->status === "draft";
    }

    /**
     * Check if submission is submitted
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ["submitted", "graded", "returned"]);
    }

    /**
     * Check if submission is graded
     */
    public function isGraded(): bool
    {
        return $this->status === "graded";
    }

    /**
     * Mark submission as submitted and check if late
     */
    public function markAsSubmitted(): void
    {
        $this->status = "submitted";
        $this->submitted_at = now();

        // Check if late
        if (
            $this->assignment->due_date &&
            $this->submitted_at->isAfter($this->assignment->due_date)
        ) {
            $this->is_late = true;
            $this->late_days = $this->submitted_at->diffInDays(
                $this->assignment->due_date,
            );
        }

        $this->save();
    }

    /**
     * Mark submission as graded
     */
    public function markAsGraded(): void
    {
        $this->status = "graded";
        $this->save();
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            "draft" => "gray",
            "submitted" => "blue",
            "graded" => "green",
            "returned" => "yellow",
            default => "gray",
        };
    }
}

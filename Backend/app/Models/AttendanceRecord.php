<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = "attendence_records";

    protected $fillable = [
        "enrollment_id",
        "attendance_session_id",
        "status",
        "notes",
        "supporting_doc_path",
        "reviewed_by",
        "reviewed_at",
    ];

    protected $casts = [
        "reviewed_at" => "datetime",
    ];

    /**
     * Get the enrollment that owns the attendance record.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the attendance session that owns the record.
     */
    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    /**
     * Get the user who reviewed the attendance record.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, "reviewed_by");
    }

    /**
     * Get the student through enrollment.
     */
    public function student()
    {
        return $this->enrollment->student;
    }

    /**
     * Get the course through enrollment.
     */
    public function course()
    {
        return $this->enrollment->course;
    }

    /**
     * Scope a query to only include present records.
     */
    public function scopePresent($query)
    {
        return $query->where("status", "present");
    }

    /**
     * Scope a query to only include absent records.
     */
    public function scopeAbsent($query)
    {
        return $query->where("status", "absent");
    }

    /**
     * Scope a query to only include pending records.
     */
    public function scopePending($query)
    {
        return $query->where("status", "pending");
    }

    /**
     * Scope a query to only include sick records.
     */
    public function scopeSick($query)
    {
        return $query->where("status", "sick");
    }

    /**
     * Scope a query to only include permission records.
     */
    public function scopePermission($query)
    {
        return $query->where("status", "permission");
    }

    /**
     * Scope a query to only include records that need review.
     */
    public function scopeNeedsReview($query)
    {
        return $query
            ->whereIn("status", ["sick", "permission"])
            ->whereNull("reviewed_by");
    }

    /**
     * Check in the attendance.
     */
    public function checkIn(): void
    {
        $this->update([
            "status" => "present",
        ]);
    }

    /**
     * Mark as sick with supporting document.
     */
    public function markAsSick(string $documentPath, string $notes = null): void
    {
        $this->update([
            "status" => "sick",
            "supporting_doc_path" => $documentPath,
            "notes" => $notes,
        ]);
    }

    /**
     * Mark as permission with supporting document.
     */
    public function markAsPermission(
        string $documentPath,
        string $notes = null,
    ): void {
        $this->update([
            "status" => "permission",
            "supporting_doc_path" => $documentPath,
            "notes" => $notes,
        ]);
    }

    /**
     * Mark as absent.
     */
    public function markAsAbsent(): void
    {
        $this->update(["status" => "absent"]);
    }

    /**
     * Approve the attendance record (for sick/permission).
     */
    public function approve(int $reviewerId, string $reviewNotes = null): void
    {
        $this->update([
            "reviewed_by" => $reviewerId,
            "reviewed_at" => now(),
            "notes" => $reviewNotes ?? $this->notes,
        ]);
    }

    /**
     * Reject the attendance record (change to absent).
     */
    public function reject(int $reviewerId, string $reviewNotes): void
    {
        $this->update([
            "status" => "absent",
            "reviewed_by" => $reviewerId,
            "reviewed_at" => now(),
            "notes" => $reviewNotes,
        ]);
    }

    /**
     * Check if the record needs review.
     */
    public function needsReview(): bool
    {
        return in_array($this->status, ["sick", "permission"]) &&
            !$this->reviewed_by;
    }

    /**
     * Check if the record has been reviewed.
     */
    public function isReviewed(): bool
    {
        return !is_null($this->reviewed_by);
    }

    /**
     * Check if the student was present.
     */
    public function isPresent(): bool
    {
        return $this->status === "present";
    }

    /**
     * Check if the student was absent.
     */
    public function isAbsent(): bool
    {
        return $this->status === "absent";
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            "present" => "Hadir",
            "absent" => "Tidak Hadir",
            "sick" => "Sakit",
            "permission" => "Izin",
            "pending" => "Menunggu",
            default => "Unknown",
        };
    }
}

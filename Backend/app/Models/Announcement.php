<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'course_id',
        'title',
        'content',
        'priority',        
        'announcement_type',
        'status',
        'published_at',
        'expires_at',
        'view_count',
        'pinned',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
        'pinned' => 'boolean',
    ];

    /**
     * Get the user who created the announcement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the course that owns the announcement (nullable for global announcements).
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope a query to only include global announcements.
     */
    public function scopeGlobal($query)
    {
        return $query->where('announcement_type', 'global')
            ->whereNull('course_id');
    }

    /**
     * Scope a query to only include course announcements.
     */
    public function scopeCourseSpecific($query)
    {
        return $query->where('announcement_type', 'course')
            ->whereNotNull('course_id');
    }

    /**
     * Scope a query to only include published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope a query to only include draft announcements.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived announcements.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to only include active announcements (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope a query to only include pinned announcements.
     */
    public function scopePinned($query)
    {
        return $query->where('pinned', true);
    }

    /**
     * Scope a query to only include high priority announcements.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope a query to only include urgent announcements.
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope for announcements visible to a specific course.
     */
    public function scopeForCourse($query, int $courseId)
    {
        return $query->where(function($q) use ($courseId) {
            $q->where('announcement_type', 'global')
              ->orWhere(function($subQ) use ($courseId) {
                  $subQ->where('announcement_type', 'course')
                       ->where('course_id', $courseId);
              });
        });
    }

    /**
     * Publish the announcement.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    /**
     * Archive the announcement.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Pin the announcement.
     */
    public function pin(): void
    {
        $this->update(['pinned' => true]);
    }

    /**
     * Unpin the announcement.
     */
    public function unpin(): void
    {
        $this->update(['pinned' => false]);
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Check if the announcement is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && (!$this->published_at || $this->published_at->isPast());
    }

    /**
     * Check if the announcement is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the announcement is active.
     */
    public function isActive(): bool
    {
        return $this->isPublished() && !$this->isExpired();
    }

    /**
     * Check if the announcement is global.
     */
    public function isGlobal(): bool
    {
        return $this->announcement_type === 'global' && !$this->course_id;
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'published' => 'green',
            'draft' => 'yellow',
            'archived' => 'gray',
            default => 'gray',
        };
    }
}

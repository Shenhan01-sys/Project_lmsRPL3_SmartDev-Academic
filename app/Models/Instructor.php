<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    protected $fillable = [
        'user_id',
        'instructor_code',
        'full_name',
        'email',
        'phone',
        'specialization',
        'education_level',
        'experience_years',
        'bio',
        'status',
    ];

    protected $casts = [
        'experience_years' => 'integer',
    ];

    /**
     * Get the user that owns the instructor (for authentication)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all courses taught by the instructor
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Get active courses taught by the instructor
     */
    public function activeCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id')
            ->where('status', 'published');
    }
}

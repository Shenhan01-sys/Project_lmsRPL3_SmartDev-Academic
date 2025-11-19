<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'relationship',
        'occupation',
        'address',
    ];

    /**
     * Get the user that owns the parent (for authentication)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all students (children) of this parent
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    /**
     * Get active students of this parent
     */
    public function activeStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id')
            ->where('status', 'active');
    }
}

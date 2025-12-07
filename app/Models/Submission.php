<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'enrollment_id',
        'file_path',
        'grade',
        'feedback',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}

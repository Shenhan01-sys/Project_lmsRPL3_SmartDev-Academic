<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'material_type',
        'content_path',
        'file_size',
    ];

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }
}

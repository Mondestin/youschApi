<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\Department;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'description',
    ];

    /**
     * Get the department that owns this course.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the subjects for this course.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the classes for this course.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }
} 
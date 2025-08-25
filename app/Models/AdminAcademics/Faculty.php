<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Department;

class Faculty extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'description',
    ];

    /**
     * Get the school that owns this faculty.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the departments for this faculty.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }   

    /**
     * Get the statistics for this faculty.
     */
    public function statistics(): array
    {
        return [
            'total_departments' => $this->departments()->count(),
            'total_courses' => $this->departments()->sum('courses_count'),
        ];
    }
} 
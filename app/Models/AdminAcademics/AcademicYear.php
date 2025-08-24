<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\StudentGrade;
use App\Models\AdminAcademics\TeacherAssignment;

class AcademicYear extends Model
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
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the school that owns this academic year.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the terms for this academic year.
     */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    /**
     * Get the student enrollments for this academic year.
     */
    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'academic_year_id');
    }

    /**
     * Get the student grades for this academic year.
     */
    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'academic_year_id');
    }

    /**
     * Get the teacher assignments for this academic year.
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'academic_year_id');
    }
} 
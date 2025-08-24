<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\StudentGrade;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject_id',
        'class_id',
        'coordinator_id',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_marks',
        'passing_marks',
        'description',
        'instructions',
        'is_active',
        'school_id',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_marks' => 'integer',
        'passing_marks' => 'integer',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    const TYPE_INTERNAL = 'internal';
    const TYPE_MIDTERM = 'midterm';
    const TYPE_FINAL = 'final';
    const TYPE_QUIZ = 'quiz';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_PROJECT = 'project';

    /**
     * Get the subject for this exam.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class for this exam.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the coordinator for this exam.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Get the school for this exam.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student grades for this exam.
     */
    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'exam_id');
    }

    /**
     * Scope to get active exams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get exams by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>=', now()->toDateString());
    }

    /**
     * Scope to get exams by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('exam_date', [$startDate, $endDate]);
    }
} 
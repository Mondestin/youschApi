<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Lab;
use App\Models\ExamsGradings\ExamType;
use App\Models\AdminAcademics\StudentGrade;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject_id',
        'class_id',
        'exam_date',
        'start_time',
        'end_time',
        'instructions',
        'lab_id',
        'exam_type_id',
        'examiner_id',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
    ];

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

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
     * Get the lab for this exam.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the exam type for this exam.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Get the examiner for this exam.
     */
    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }

    /**
     * Get the student grades for this exam.
     */
    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'exam_id');
    }

    /**
     * Scope to get exams by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get exams by exam type.
     */
    public function scopeByExamType($query, $examTypeId)
    {
        return $query->where('exam_type_id', $examTypeId);
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
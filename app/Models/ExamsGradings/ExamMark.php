<?php

namespace App\Models\ExamsGradings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ExamMark extends Model
{
    use HasFactory;

    protected $table = 'exam_marks';

    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
        'grade',
        'remarks',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
    ];

    /**
     * Get the exam for this mark.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student for this mark.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'student_id');
    }

    /**
     * Scope to get marks by exam.
     */
    public function scopeByExam(Builder $query, int $examId): Builder
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Scope to get marks by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get marks with grades.
     */
    public function scopeWithGrades(Builder $query): Builder
    {
        return $query->whereNotNull('grade');
    }

    /**
     * Scope to get marks without grades.
     */
    public function scopeWithoutGrades(Builder $query): Builder
    {
        return $query->whereNull('grade');
    }

    /**
     * Check if the student passed the exam.
     */
    public function isPassing(): bool
    {
        if (!$this->grade) {
            return false;
        }
        
        return !in_array($this->grade, ['F', 'E']);
    }

    /**
     * Check if the student failed the exam.
     */
    public function isFailing(): bool
    {
        return !$this->isPassing();
    }

    /**
     * Get the percentage score.
     */
    public function getPercentageAttribute(): ?float
    {
        if (!$this->marks_obtained || !$this->exam) {
            return null;
        }
        
        return ($this->marks_obtained / $this->exam->total_marks) * 100;
    }

    /**
     * Get the grade color for UI display.
     */
    public function getGradeColorAttribute(): string
    {
        if (!$this->grade) {
            return 'secondary';
        }

        return match($this->grade) {
            'A+', 'A', 'A-' => 'success',
            'B+', 'B', 'B-' => 'info',
            'C+', 'C', 'C-' => 'warning',
            'D+', 'D', 'D-' => 'warning',
            'F', 'E' => 'danger',
            default => 'secondary',
        };
    }
} 
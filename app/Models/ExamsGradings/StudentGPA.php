<?php

namespace App\Models\ExamsGradings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentGPA extends Model
{
    use HasFactory;

    protected $table = 'student_gpa';

    protected $fillable = [
        'student_id',
        'term_id',
        'academic_year_id',
        'gpa',
        'cgpa',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
    ];

    /**
     * Get the student for this GPA record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'student_id');
    }

    /**
     * Get the term for this GPA record.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AdminAcademics\Term::class);
    }

    /**
     * Get the academic year for this GPA record.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AdminAcademics\AcademicYear::class);
    }

    /**
     * Scope to get GPA records by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get GPA records by term.
     */
    public function scopeByTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to get GPA records by academic year.
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to get GPA records with high performance.
     */
    public function scopeHighPerformance(Builder $query, float $threshold = 3.5): Builder
    {
        return $query->where('gpa', '>=', $threshold);
    }

    /**
     * Scope to get GPA records with low performance.
     */
    public function scopeLowPerformance(Builder $query, float $threshold = 2.0): Builder
    {
        return $query->where('gpa', '<', $threshold);
    }

    /**
     * Check if the GPA is excellent.
     */
    public function isExcellent(): bool
    {
        return $this->gpa >= 3.8;
    }

    /**
     * Check if the GPA is good.
     */
    public function isGood(): bool
    {
        return $this->gpa >= 3.0 && $this->gpa < 3.8;
    }

    /**
     * Check if the GPA is satisfactory.
     */
    public function isSatisfactory(): bool
    {
        return $this->gpa >= 2.0 && $this->gpa < 3.0;
    }

    /**
     * Check if the GPA is poor.
     */
    public function isPoor(): bool
    {
        return $this->gpa < 2.0;
    }

    /**
     * Get the GPA status for display.
     */
    public function getGpaStatusAttribute(): string
    {
        if ($this->isExcellent()) return 'Excellent';
        if ($this->isGood()) return 'Good';
        if ($this->isSatisfactory()) return 'Satisfactory';
        return 'Poor';
    }

    /**
     * Get the GPA color for UI display.
     */
    public function getGpaColorAttribute(): string
    {
        if ($this->isExcellent()) return 'success';
        if ($this->isGood()) return 'info';
        if ($this->isSatisfactory()) return 'warning';
        return 'danger';
    }
} 
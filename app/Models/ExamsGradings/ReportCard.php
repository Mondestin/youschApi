<?php

namespace App\Models\ExamsGradings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ReportCard extends Model
{
    use HasFactory;

    protected $table = 'report_cards';

    protected $fillable = [
        'student_id',
        'class_id',
        'term_id',
        'academic_year_id',
        'gpa',
        'cgpa',
        'remarks',
        'issued_date',
        'format',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'issued_date' => 'date',
    ];

    /**
     * Get the student for this report card.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'student_id');
    }

    /**
     * Get the class for this report card.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AdminAcademics\ClassRoom::class, 'class_id');
    }

    /**
     * Get the term for this report card.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AdminAcademics\Term::class);
    }

    /**
     * Get the academic year for this report card.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AdminAcademics\AcademicYear::class);
    }

    /**
     * Scope to get report cards by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get report cards by class.
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get report cards by term.
     */
    public function scopeByTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to get report cards by academic year.
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to get report cards by format.
     */
    public function scopeByFormat(Builder $query, string $format): Builder
    {
        return $query->where('format', $format);
    }

    /**
     * Scope to get recent report cards.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('issued_date', '>=', now()->subDays($days));
    }

    /**
     * Check if the report card is in PDF format.
     */
    public function isPDF(): bool
    {
        return $this->format === 'PDF';
    }

    /**
     * Check if the report card is in digital format.
     */
    public function isDigital(): bool
    {
        return $this->format === 'Digital';
    }

    /**
     * Check if the student has excellent performance.
     */
    public function hasExcellentPerformance(): bool
    {
        return $this->gpa >= 3.8;
    }

    /**
     * Check if the student has good performance.
     */
    public function hasGoodPerformance(): bool
    {
        return $this->gpa >= 3.0 && $this->gpa < 3.8;
    }

    /**
     * Check if the student has satisfactory performance.
     */
    public function hasSatisfactoryPerformance(): bool
    {
        return $this->gpa >= 2.0 && $this->gpa < 3.0;
    }

    /**
     * Check if the student has poor performance.
     */
    public function hasPoorPerformance(): bool
    {
        return $this->gpa < 2.0;
    }

    /**
     * Get the performance status for display.
     */
    public function getPerformanceStatusAttribute(): string
    {
        if ($this->hasExcellentPerformance()) return 'Excellent';
        if ($this->hasGoodPerformance()) return 'Good';
        if ($this->hasSatisfactoryPerformance()) return 'Satisfactory';
        return 'Poor';
    }

    /**
     * Get the performance color for UI display.
     */
    public function getPerformanceColorAttribute(): string
    {
        if ($this->hasExcellentPerformance()) return 'success';
        if ($this->hasGoodPerformance()) return 'info';
        if ($this->hasSatisfactoryPerformance()) return 'warning';
        return 'danger';
    }
} 
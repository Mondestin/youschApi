<?php

namespace App\Models\Students;

use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\AcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AcademicHistory extends Model
{
    use HasFactory;

    protected $table = 'academic_history';

    protected $fillable = [
        'student_id',
        'subject_id',
        'class_id',
        'term_id',
        'academic_year_id',
        'marks',
        'grade',
        'gpa',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'gpa' => 'decimal:2',
    ];

    /**
     * Get the student for this academic record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the subject for this academic record.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class for this academic record.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the term for this academic record.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the academic year for this academic record.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope to get records by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get records by subject.
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to get records by class.
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get records by term.
     */
    public function scopeByTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to get records by academic year.
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to get records with passing grades.
     */
    public function scopePassing(Builder $query): Builder
    {
        return $query->whereNotNull('grade')->where('grade', '!=', 'F');
    }

    /**
     * Scope to get records with failing grades.
     */
    public function scopeFailing(Builder $query): Builder
    {
        return $query->where('grade', 'F');
    }

    /**
     * Check if the grade is passing.
     */
    public function isPassing(): bool
    {
        return $this->grade && $this->grade !== 'F';
    }

    /**
     * Check if the grade is failing.
     */
    public function isFailing(): bool
    {
        return $this->grade === 'F';
    }

    /**
     * Get the grade point value.
     */
    public function getGradePointAttribute(): float
    {
        $gradePoints = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F' => 0.0
        ];

        return $gradePoints[$this->grade] ?? 0.0;
    }
} 
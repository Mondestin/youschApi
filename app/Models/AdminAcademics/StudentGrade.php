<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\School;

class StudentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'class_id',
        'exam_id',
        'academic_year_id',
        'term_id',
        'grade',
        'score',
        'max_score',
        'percentage',
        'remarks',
        'graded_by',
        'graded_at',
        'school_id',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    /**
     * Get the student that owns the grade.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the subject for this grade.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class for this grade.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the exam for this grade.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the academic year for this grade.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the term for this grade.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the teacher who graded this.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the school for this grade.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
} 
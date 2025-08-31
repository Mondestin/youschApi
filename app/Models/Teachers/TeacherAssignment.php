<?php

namespace App\Models\Teachers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $table = 'teacher_assignments';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'academic_year',
        'term',
        'start_date',
        'end_date',
        'hours_per_week',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the teacher assigned to this assignment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class for this assignment.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the subject for this assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the timetables for this assignment.
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(TeacherTimetable::class, 'assignment_id');
    }

    /**
     * Scope query to active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to assignments by academic year.
     */
    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    /**
     * Scope query to assignments by term.
     */
    public function scopeByTerm($query, $term)
    {
        return $query->where('term', $term);
    }

    /**
     * Scope query to assignments by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query to assignments by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query to assignments by subject.
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope query to current assignments.
     */
    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('is_active', true);
    }

    /**
     * Check if assignment is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        return $this->is_active && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    /**
     * Get the duration of the assignment in days.
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get the total hours for this assignment.
     */
    public function getTotalHours(): float
    {
        $weeks = $this->start_date->diffInWeeks($this->end_date);
        return $weeks * $this->hours_per_week;
    }

    /**
     * Get the workload percentage for this teacher.
     */
    public function getWorkloadPercentage(): float
    {
        // Assuming 40 hours per week is 100% workload
        return ($this->hours_per_week / 40) * 100;
    }
} 
<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $table = 'teacher_attendance';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'lab_id',
        'timetable_id',
        'date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Status constants
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';

    /**
     * Get the teacher for this attendance record.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Teachers\Teacher::class);
    }

    /**
     * Get the class for this attendance record.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Class::class, 'class_id');
    }

    /**
     * Get the subject for this attendance record.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Subject::class);
    }

    /**
     * Get the lab for this attendance record.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Lab::class);
    }

    /**
     * Get the timetable entry for this attendance record.
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Teachers\TeacherTimetable::class, 'timetable_id');
    }

    /**
     * Scope query to attendance by teacher.
     */
    public function scopeByTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query to attendance by class.
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query to attendance by subject.
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope query to attendance by date.
     */
    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    /**
     * Scope query to attendance by date range.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope query to attendance by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query to attendance by term.
     */
    public function scopeByTerm(Builder $query, string $term): Builder
    {
        return $query->whereHas('timetable', function ($q) use ($term) {
            $q->where('term', $term);
        });
    }

    /**
     * Scope query to attendance by academic year.
     */
    public function scopeByAcademicYear(Builder $query, string $academicYear): Builder
    {
        return $query->whereHas('timetable', function ($q) use ($academicYear) {
            $q->where('academic_year', $academicYear);
        });
    }

    /**
     * Check if attendance is for today.
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Check if teacher was present.
     */
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_PRESENT;
    }

    /**
     * Check if teacher was absent.
     */
    public function isAbsent(): bool
    {
        return $this->status === self::STATUS_ABSENT;
    }

    /**
     * Check if teacher was late.
     */
    public function isLate(): bool
    {
        return $this->status === self::STATUS_LATE;
    }

    /**
     * Get attendance status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PRESENT => 'success',
            self::STATUS_ABSENT => 'danger',
            self::STATUS_LATE => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get attendance status text for UI.
     */
    public function getStatusTextAttribute(): string
    {
        return ucfirst($this->status);
    }
} 
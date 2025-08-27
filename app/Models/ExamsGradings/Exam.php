<?php

namespace App\Models\ExamsGradings;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Lab;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'subject_id',
        'lab_id',
        'exam_type_id',
        'exam_date',
        'start_time',
        'end_time',
        'examiner_id',
        'instructions',
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
     * Get the class for this exam.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the subject for this exam.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
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
     * Get the exam marks for this exam.
     */
    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    /**
     * Scope to get scheduled exams.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope to get completed exams.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get cancelled exams.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope to get upcoming exams.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('exam_date', '>=', now()->toDateString())
                    ->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope to get past exams.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('exam_date', '<', now()->toDateString());
    }

    /**
     * Scope to get exams by date range.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('exam_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get exams by class.
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get exams by subject.
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to get exams by examiner.
     */
    public function scopeByExaminer(Builder $query, int $examinerId): Builder
    {
        return $query->where('examiner_id', $examinerId);
    }

    /**
     * Scope to get exams by exam type.
     */
    public function scopeByExamType(Builder $query, int $examTypeId): Builder
    {
        return $query->where('exam_type_id', $examTypeId);
    }

    /**
     * Check if the exam is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    /**
     * Check if the exam is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the exam is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the exam is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->isScheduled() && $this->exam_date >= now()->toDateString();
    }

    /**
     * Check if the exam is today.
     */
    public function isToday(): bool
    {
        return $this->exam_date->isToday();
    }

    /**
     * Get the duration of the exam in minutes.
     */
    public function getDurationAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        return $start->diffInMinutes($end);
    }

    /**
     * Get the exam status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the exam status text for display.
     */
    public function getStatusTextAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Mark the exam as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark the exam as cancelled.
     */
    public function markAsCancelled(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Get the total number of students who took this exam.
     */
    public function getTotalStudentsAttribute(): int
    {
        return $this->examMarks()->count();
    }

    /**
     * Get the number of students who passed this exam.
     */
    public function getPassingStudentsAttribute(): int
    {
        return $this->examMarks()->whereHas('grade', function($query) {
            $query->whereNotIn('grade', ['F', 'E']);
        })->count();
    }

    /**
     * Get the pass rate for this exam.
     */
    public function getPassRateAttribute(): float
    {
        $total = $this->total_students;
        if ($total === 0) return 0.0;
        
        return round(($this->passing_students / $total) * 100, 2);
    }
} 
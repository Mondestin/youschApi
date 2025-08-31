<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentAttendanceExcuse extends Model
{
    use HasFactory;

    protected $table = 'student_attendance_excuses';

    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'lab_id',
        'date',
        'reason',
        'document_path',
        'status',
        'reviewed_by',
        'reviewed_on',
    ];

    protected $casts = [
        'date' => 'date',
        'reviewed_on' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the student for this excuse request.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Students\Student::class);
    }

    /**
     * Get the class for this excuse request.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Class::class, 'class_id');
    }

    /**
     * Get the subject for this excuse request.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Subject::class);
    }

    /**
     * Get the lab for this excuse request.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Lab::class);
    }

    /**
     * Get the reviewer for this excuse request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /**
     * Scope query to excuses by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query to excuses by class.
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query to excuses by subject.
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope query to excuses by date.
     */
    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    /**
     * Scope query to excuses by date range.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope query to excuses by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query to pending excuses.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to approved excuses.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope query to rejected excuses.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if excuse is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if excuse is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if excuse is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if excuse has been reviewed.
     */
    public function isReviewed(): bool
    {
        return !is_null($this->reviewed_by);
    }

    /**
     * Approve the excuse request.
     */
    public function approve(int $reviewerId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_on' => now(),
        ]);
    }

    /**
     * Reject the excuse request.
     */
    public function reject(int $reviewerId): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_on' => now(),
        ]);
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status text for UI.
     */
    public function getStatusTextAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get document URL if document exists.
     */
    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? asset('storage/' . $this->document_path) : null;
    }
} 
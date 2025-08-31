<?php

namespace App\Models\Teachers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherLeave extends Model
{
    use HasFactory;

    protected $table = 'teacher_leaves';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'leave_type',
        'start_date',
        'end_date',
        'status',
        'applied_on',
        'reviewed_by',
        'reviewed_on',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'applied_on' => 'datetime',
        'reviewed_on' => 'datetime',
    ];

    // Leave type constants
    const TYPE_VACATION = 'vacation';
    const TYPE_SICK = 'sick';
    const TYPE_UNPAID = 'unpaid';
    const TYPE_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the teacher for this leave request.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the reviewer for this leave request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /**
     * Get the duration of the leave in days.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if leave is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if leave is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if leave is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if leave is currently active.
     */
    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $this->start_date <= $today && $this->end_date >= $today;
    }

   
    /**
     * Check if leave is in the future.
     */
    public function isFuture(): bool
    {
        return $this->start_date > now()->toDateString();
    }

    /**
     * Check if leave is in the past.
     */
    public function isPast(): bool
    {
        return $this->end_date < now()->toDateString();
    }

    /**
     * Approve the leave request.
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
     * Reject the leave request.
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
     * Scope query to leaves by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query to leaves by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('leave_type', $type);
    }

    /**
     * Scope query to leaves by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query to pending leaves.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to approved leaves.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope query to rejected leaves.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope query to leaves by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Scope query to active leaves.
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }
} 
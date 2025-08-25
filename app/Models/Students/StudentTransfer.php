<?php

namespace App\Models\Students;

use App\Models\User;
use App\Models\AdminAcademics\Campus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_campus_id',
        'to_campus_id',
        'request_date',
        'approved_date',
        'status',
        'reviewer_id',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_date' => 'date',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the student for this transfer.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the source campus for this transfer.
     */
    public function fromCampus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'from_campus_id');
    }

    /**
     * Get the destination campus for this transfer.
     */
    public function toCampus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'to_campus_id');
    }

    /**
     * Get the reviewer for this transfer.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Scope to get pending transfers.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get approved transfers.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get rejected transfers.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope to get transfers by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get transfers by campus.
     */
    public function scopeByCampus(Builder $query, int $campusId): Builder
    {
        return $query->where(function($q) use ($campusId) {
            $q->where('from_campus_id', $campusId)
              ->orWhere('to_campus_id', $campusId);
        });
    }

    /**
     * Check if transfer is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transfer is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if transfer is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the transfer.
     */
    public function approve(int $reviewerId): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_date' => now(),
            'reviewer_id' => $reviewerId,
        ]);
    }

    /**
     * Reject the transfer.
     */
    public function reject(int $reviewerId): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewer_id' => $reviewerId,
        ]);
    }

    /**
     * Get the transfer duration in days.
     */
    public function getTransferDurationAttribute(): int
    {
        if (!$this->approved_date) {
            return 0;
        }

        return $this->request_date->diffInDays($this->approved_date);
    }
} 
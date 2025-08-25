<?php

namespace App\Models\Students;

use App\Models\User;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'campus_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'email',
        'phone',
        'parent_name',
        'parent_email',
        'parent_phone',
        'status',
        'applied_on',
        'reviewed_on',
        'reviewer_id',
    ];

    protected $casts = [
        'dob' => 'date',
        'applied_on' => 'datetime',
        'reviewed_on' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Gender constants
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';

    /**
     * Get the school for this application.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the campus for this application.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the reviewer for this application.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the full name of the applicant.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the age of the applicant.
     */
    public function getAgeAttribute(): int
    {
        return $this->dob->age;
    }

    /**
     * Scope to get pending applications.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get approved applications.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get rejected applications.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope to get applications by school.
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to get applications by campus.
     */
    public function scopeByCampus(Builder $query, int $campusId): Builder
    {
        return $query->where('campus_id', $campusId);
    }

    /**
     * Check if application is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if application is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if application is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the application.
     */
    public function approve(int $reviewerId): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_on' => now(),
            'reviewer_id' => $reviewerId,
        ]);
    }

    /**
     * Reject the application.
     */
    public function reject(int $reviewerId): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_on' => now(),
            'reviewer_id' => $reviewerId,
        ]);
    }
} 
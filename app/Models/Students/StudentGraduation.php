<?php

namespace App\Models\Students;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentGraduation extends Model
{
    use HasFactory;

    protected $table = 'student_graduation';

    protected $fillable = [
        'student_id',
        'graduation_date',
        'diploma_number',
        'status',
    ];

    protected $casts = [
        'graduation_date' => 'date',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ISSUED = 'issued';

    /**
     * Get the student for this graduation record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Scope to get pending graduations.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get issued graduations.
     */
    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    /**
     * Scope to get graduations by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get graduations by date range.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('graduation_date', [$startDate, $endDate]);
    }

    /**
     * Check if graduation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if graduation is issued.
     */
    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    /**
     * Issue the diploma.
     */
    public function issue(): bool
    {
        return $this->update(['status' => self::STATUS_ISSUED]);
    }

    /**
     * Get the graduation year.
     */
    public function getGraduationYearAttribute(): int
    {
        return $this->graduation_date->year;
    }

    /**
     * Get the graduation month.
     */
    public function getGraduationMonthAttribute(): int
    {
        return $this->graduation_date->month;
    }

    /**
     * Get the graduation day.
     */
    public function getGraduationDayAttribute(): int
    {
        return $this->graduation_date->day;
    }
} 
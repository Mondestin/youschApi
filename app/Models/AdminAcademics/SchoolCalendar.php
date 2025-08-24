<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\School;

class SchoolCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'title',
        'type',
        'start_date',
        'end_date',
        'description',
        'is_recurring',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    const TYPE_HOLIDAY = 'holiday';
    const TYPE_ACADEMIC_YEAR_START = 'academic_year_start';
    const TYPE_ACADEMIC_YEAR_END = 'academic_year_end';
    const TYPE_EXAM_PERIOD = 'exam_period';
    const TYPE_OTHER = 'other';

    /**
     * Get the school for this calendar entry.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope to get entries by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get entries by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Scope to get recurring entries.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope to get non-recurring entries.
     */
    public function scopeNonRecurring($query)
    {
        return $query->where('is_recurring', false);
    }
} 
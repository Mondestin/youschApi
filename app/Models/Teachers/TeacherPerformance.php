<?php

namespace App\Models\Teachers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPerformance extends Model
{
    use HasFactory;

    protected $table = 'teacher_performance';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'evaluation_period',
        'evaluation_date',
        'overall_rating',
        'teaching_quality',
        'classroom_management',
        'student_engagement',
        'communication_skills',
        'professional_development',
        'attendance_punctuality',
        'student_feedback_score',
        'peer_review_score',
        'supervisor_rating',
        'comments',
        'recommendations',
        'evaluated_by',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'overall_rating' => 'decimal:2',
        'teaching_quality' => 'decimal:2',
        'classroom_management' => 'decimal:2',
        'student_engagement' => 'decimal:2',
        'communication_skills' => 'decimal:2',
        'professional_development' => 'decimal:2',
        'attendance_punctuality' => 'decimal:2',
        'student_feedback_score' => 'decimal:2',
        'peer_review_score' => 'decimal:2',
        'supervisor_rating' => 'decimal:2',
    ];

    // Evaluation period constants
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_SEMESTER = 'semester';
    const PERIOD_ANNUAL = 'annual';

    /**
     * Get the teacher for this performance record.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the evaluator for this performance record.
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'evaluated_by');
    }

    /**
     * Get the average rating across all categories.
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = [
            $this->teaching_quality,
            $this->classroom_management,
            $this->student_engagement,
            $this->communication_skills,
            $this->professional_development,
            $this->attendance_punctuality,
        ];

        $validRatings = array_filter($ratings, function($rating) {
            return $rating !== null;
        });

        return count($validRatings) > 0 ? round(array_sum($validRatings) / count($validRatings), 2) : 0;
    }

    /**
     * Get the performance grade.
     */
    public function getPerformanceGradeAttribute(): string
    {
        $rating = $this->overall_rating;

        if ($rating >= 4.5) return 'A+';
        if ($rating >= 4.0) return 'A';
        if ($rating >= 3.5) return 'B+';
        if ($rating >= 3.0) return 'B';
        if ($rating >= 2.5) return 'C+';
        if ($rating >= 2.0) return 'C';
        if ($rating >= 1.5) return 'D+';
        if ($rating >= 1.0) return 'D';
        return 'F';
    }

    /**
     * Check if performance is excellent.
     */
    public function isExcellent(): bool
    {
        return $this->overall_rating >= 4.5;
    }

    /**
     * Check if performance is good.
     */
    public function isGood(): bool
    {
        return $this->overall_rating >= 3.5 && $this->overall_rating < 4.5;
    }

    /**
     * Check if performance is satisfactory.
     */
    public function isSatisfactory(): bool
    {
        return $this->overall_rating >= 2.5 && $this->overall_rating < 3.5;
    }

    /**
     * Check if performance needs improvement.
     */
    public function needsImprovement(): bool
    {
        return $this->overall_rating < 2.5;
    }

    /**
     * Scope query to performance by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query to performance by period.
     */
    public function scopeByPeriod($query, $period)
    {
        return $query->where('evaluation_period', $period);
    }

    /**
     * Scope query to performance by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('evaluation_date', [$startDate, $endDate]);
    }

    /**
     * Scope query to excellent performance.
     */
    public function scopeExcellent($query)
    {
        return $query->where('overall_rating', '>=', 4.5);
    }

    /**
     * Scope query to good performance.
     */
    public function scopeGood($query)
    {
        return $query->where('overall_rating', '>=', 3.5)->where('overall_rating', '<', 4.5);
    }

    /**
     * Scope query to satisfactory performance.
     */
    public function scopeSatisfactory($query)
    {
        return $query->where('overall_rating', '>=', 2.5)->where('overall_rating', '<', 3.5);
    }

    /**
     * Scope query to performance needing improvement.
     */
    public function scopeNeedsImprovement($query)
    {
        return $query->where('overall_rating', '<', 2.5);
    }

    /**
     * Scope query to recent performance evaluations.
     */
    public function scopeRecent($query, $days = 365)
    {
        return $query->where('evaluation_date', '>=', now()->subDays($days));
    }
} 
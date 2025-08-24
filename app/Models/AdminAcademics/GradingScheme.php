<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\GradeScale;   
use App\Models\User;

class GradingScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'school_id',
        'is_active',
        'min_score',
        'max_score',
        'passing_score',
        'grade_scale_type',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passing_score' => 'decimal:2',
    ];

    const GRADE_SCALE_TYPE_LETTER = 'letter';
    const GRADE_SCALE_TYPE_NUMERIC = 'numeric';
    const GRADE_SCALE_TYPE_PERCENTAGE = 'percentage';

    /**
     * Get the school for this grading scheme.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user who created this grading scheme.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the grade scales for this grading scheme.
     */
    public function gradeScales(): HasMany
    {
        return $this->hasMany(GradeScale::class);
    }

    /**
     * Scope to get active grading schemes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get grading schemes by school.
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Calculate grade based on score.
     */
    public function calculateGrade($score)
    {
        $gradeScale = $this->gradeScales()
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();

        return $gradeScale ? $gradeScale->grade : null;
    }

    /**
     * Check if score is passing.
     */
    public function isPassing($score)
    {
        return $score >= $this->passing_score;
    }
} 
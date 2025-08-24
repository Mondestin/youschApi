<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\GradingScheme;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'grading_scheme_id',
        'grade',
        'min_score',
        'max_score',
        'grade_point',
        'description',
        'is_passing',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'is_passing' => 'boolean',
    ];

    /**
     * Get the grading scheme for this grade scale.
     */
    public function gradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class);
    }

    /**
     * Scope to get passing grades.
     */
    public function scopePassing($query)
    {
        return $query->where('is_passing', true);
    }

    /**
     * Scope to get failing grades.
     */
    public function scopeFailing($query)
    {
        return $query->where('is_passing', false);
    }

    /**
     * Check if a score falls within this grade scale.
     */
    public function isScoreInRange($score)
    {
        return $score >= $this->min_score && $score <= $this->max_score;
    }
} 
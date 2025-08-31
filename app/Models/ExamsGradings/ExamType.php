<?php

namespace App\Models\ExamsGradings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AdminAcademics\Exam;

class ExamType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Get the exams for this exam type.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'type');
    }

    /**
     * Scope to get exam types by weight.
     */
    public function scopeByWeight(Builder $query, float $weight): Builder
    {
        return $query->where('weight', $weight);
    }

    /**
     * Scope to get exam types with weight greater than.
     */
    public function scopeWithWeightGreaterThan(Builder $query, float $weight): Builder
    {
        return $query->where('weight', '>', $weight);
    }

    /**
     * Check if this exam type is weighted.
     */
    public function isWeighted(): bool
    {
        return $this->weight != 100.0;
    }

    /**
     * Get the weight percentage.
     */
    public function getWeightPercentageAttribute(): string
    {
        return $this->weight . '%';
    }
} 
<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\Subject;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'schedule',
    ];

    /**
     * Get the subject for this lab.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
} 
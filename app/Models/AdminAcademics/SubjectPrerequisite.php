<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\Subject;


class SubjectPrerequisite extends Model
{
    use HasFactory;

    protected $table = 'subject_prerequisites';

    protected $fillable = [
        'subject_id',
        'prerequisite_id',
    ];

    /**
     * Get the subject that has this prerequisite.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the prerequisite subject.
     */
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'prerequisite_id');
    }
} 
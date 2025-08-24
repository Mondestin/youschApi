<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;

class ClassSubject extends Model
{
    use HasFactory;

    protected $table = 'class_subjects';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
    ];

    /**
     * Get the class for this subject assignment.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the subject for this class assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher assigned to this class-subject combination.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
} 
<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Course;
use App\Models\User;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'schedule',
        'assistant_id',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    /**
     * Get the subject for this lab.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the course for this lab through the subject.
     */
    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            Subject::class,
            'id', // Foreign key on subjects table
            'id', // Foreign key on courses table
            'subject_id', // Local key on labs table
            'course_id' // Local key on subjects table
        );
    }

    /**
     * Get the coordinator for this lab through the subject.
     */
    public function coordinator()
    {
        return $this->hasOneThrough(
            User::class,
            Subject::class,
            'id', // Foreign key on subjects table
            'id', // Foreign key on users table
            'subject_id', // Local key on labs table
            'coordinator_id' // Local key on subjects table
        );
    }

    /**
     * Get the lab assistant for this lab.
     */
    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }
} 
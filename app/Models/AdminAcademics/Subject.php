<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\AdminAcademics\Course;
use App\Models\AdminAcademics\Lab;  
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\StudentGrade;
use App\Models\AdminAcademics\TeacherAssignment;



class Subject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'name',
        'code',
        'description',
        'coordinator_id',
    ];

    /**
     * Get the course that owns this subject.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the coordinator (teacher) for this subject.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Get the labs for this subject.
     */
    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class);
    }

    /**
     * Get the prerequisites for this subject.
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_prerequisites', 'subject_id', 'prerequisite_id');
    }

    /**
     * Get the subjects that have this subject as a prerequisite.
     */
    public function requiredBy(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_prerequisites', 'prerequisite_id', 'subject_id');
    }

    /**
     * Get the classes that teach this subject.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_subjects', 'subject_id', 'class_id');
    }

    /**
     * Get the exams for this subject.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'subject_id');
    }

    /**
     * Get the student grades for this subject.
     */
    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'subject_id');
    }

    /**
     * Get the teacher assignments for this subject.
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'subject_id');
    }
} 
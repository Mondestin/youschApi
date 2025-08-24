<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\Course;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Timetable;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\TeacherAssignment;
use App\Models\AdminAcademics\Announcement;

class ClassRoom extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campus_id',
        'course_id',
        'name',
        'capacity',
    ];

    /**
     * Get the campus that owns this class.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the course that owns this class.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the subjects taught in this class.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id')
                    ->withPivot('teacher_id');
    }

    /**
     * Get the teachers assigned to this class.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_subjects', 'class_id', 'teacher_id');
    }

    /**
     * Get the timetables for this class.
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'class_id');
    }

    /**
     * Get the exams for this class.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    /**
     * Get the student enrollments for this class.
     */
    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'class_id');
    }

    /**
     * Get the teacher assignments for this class.
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    /**
     * Get the announcements for this class.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }
} 
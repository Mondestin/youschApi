<?php

namespace App\Models\Teachers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Lab;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'school_id',
        'campus_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'email',
        'phone',
        'address',
        'hire_date',
        'status',
        'profile_picture',
    ];

    protected $casts = [
        'dob' => 'date',
        'hire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_RESIGNED = 'resigned';
    const STATUS_SUSPENDED = 'suspended';

    // Gender constants
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';

    /**
     * Get the teacher's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the teacher's age.
     */
    public function getAgeAttribute(): int
    {
        return $this->dob->age;
    }

    /**
     * Get the teacher's years of service.
     */
    public function getYearsOfServiceAttribute(): int
    {
        return $this->hire_date->diffInYears(now());
    }

    /**
     * Check if teacher is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if teacher is on leave.
     */
    public function isOnLeave(): bool
    {
        return $this->status === self::STATUS_ON_LEAVE;
    }

    /**
     * Check if teacher has resigned.
     */
    public function hasResigned(): bool
    {
        return $this->status === self::STATUS_RESIGNED;
    }

    /**
     * Check if teacher is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Get the school that the teacher belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the campus that the teacher belongs to.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the teacher's documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    /**
     * Get the teacher's assignments.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * Get the teacher's timetables.
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(TeacherTimetable::class);
    }

    /**
     * Get the teacher's leaves.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    /**
     * Get the teacher's performance records.
     */
    public function performanceRecords(): HasMany
    {
        return $this->hasMany(TeacherPerformance::class);
    }

    /**
     * Get the teacher's current class assignments.
     */
    public function currentAssignments()
    {
        return $this->assignments()
            ->whereHas('term', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['classRoom', 'subject', 'lab', 'term', 'academicYear']);
    }

    /**
     * Get the teacher's workload for a specific term.
     */
    public function getWorkloadForTerm($termId)
    {
        return $this->assignments()
            ->where('term_id', $termId)
            ->with(['classRoom', 'subject', 'lab'])
            ->get();
    }

    /**
     * Get the teacher's schedule for a specific date.
     */
    public function getScheduleForDate($date)
    {
        return $this->timetables()
            ->where('date', $date)
            ->with(['classRoom', 'subject', 'lab'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get the teacher's pending leave requests.
     */
    public function getPendingLeaves()
    {
        return $this->leaves()
            ->where('status', 'pending')
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Scope query to active teachers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope query to teachers on leave.
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', self::STATUS_ON_LEAVE);
    }

    /**
     * Scope query to teachers by campus.
     */
    public function scopeByCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    /**
     * Scope query to teachers by school.
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope query to teachers by hire date range.
     */
    public function scopeByHireDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('hire_date', [$startDate, $endDate]);
    }
} 
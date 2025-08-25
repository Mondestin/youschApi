<?php

namespace App\Models\Students;

use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\StudentGrade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'campus_id',
        'class_id',
        'student_number',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'email',
        'phone',
        'parent_name',
        'parent_email',
        'parent_phone',
        'enrollment_date',
        'status',
        'profile_picture',
    ];

    protected $casts = [
        'dob' => 'date',
        'enrollment_date' => 'date',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_GRADUATED = 'graduated';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_INACTIVE = 'inactive';

    // Gender constants
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';

    /**
     * Get the school for this student.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the campus for this student.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the current class for this student.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the student enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Get the student grades.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    /**
     * Get the academic history.
     */
    public function academicHistory(): HasMany
    {
        return $this->hasMany(AcademicHistory::class);
    }

    /**
     * Get the student transfers.
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class);
    }

    /**
     * Get the student graduation record.
     */
    public function graduation(): HasMany
    {
        return $this->hasMany(StudentGraduation::class);
    }

    /**
     * Get the student documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    /**
     * Get the full name of the student.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the age of the student.
     */
    public function getAgeAttribute(): int
    {
        return $this->dob->age;
    }

    /**
     * Scope to get active students.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get graduated students.
     */
    public function scopeGraduated(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_GRADUATED);
    }

    /**
     * Scope to get students by school.
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to get students by campus.
     */
    public function scopeByCampus(Builder $query, int $campusId): Builder
    {
        return $query->where('campus_id', $campusId);
    }

    /**
     * Scope to get students by class.
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Check if student is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if student is graduated.
     */
    public function isGraduated(): bool
    {
        return $this->status === self::STATUS_GRADUATED;
    }

    /**
     * Check if student is transferred.
     */
    public function isTransferred(): bool
    {
        return $this->status === self::STATUS_TRANSFERRED;
    }

    /**
     * Check if student is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Activate the student.
     */
    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Suspend the student.
     */
    public function suspend(): bool
    {
        return $this->update(['status' => self::STATUS_SUSPENDED]);
    }

    /**
     * Mark student as graduated.
     */
    public function graduate(): bool
    {
        return $this->update(['status' => self::STATUS_GRADUATED]);
    }

    /**
     * Mark student as transferred.
     */
    public function transfer(): bool
    {
        return $this->update(['status' => self::STATUS_TRANSFERRED]);
    }
} 
<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\School;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'enrollment_date',
        'status',
        'enrolled_by',
        'notes',
        'school_id',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_GRADUATED = 'graduated';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Get the student for this enrollment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class for this enrollment.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the academic year for this enrollment.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the staff member who enrolled the student.
     */
    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    /**
     * Get the school for this enrollment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope to get active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get enrollments by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
} 
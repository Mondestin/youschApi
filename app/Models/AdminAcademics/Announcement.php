<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\ClassRoom;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'scope',
        'priority',
        'school_id',
        'campus_id',
        'class_id',
        'is_active',
        'is_urgent',
        'publish_date',
        'expiry_date',
        'created_by',
        'target_audience',
        'attachments',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_urgent' => 'boolean',
        'publish_date' => 'datetime',
        'expiry_date' => 'datetime',
        'attachments' => 'array',
    ];

    const SCOPE_SCHOOL = 'school';
    const SCOPE_CAMPUS = 'campus';
    const SCOPE_CLASS = 'class';
    const SCOPE_DEPARTMENT = 'department';
    const SCOPE_FACULTY = 'faculty';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const TARGET_STUDENTS = 'students';
    const TARGET_TEACHERS = 'teachers';
    const TARGET_ADMIN = 'admin';
    const TARGET_ALL = 'all';

    /**
     * Get the school for this announcement.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the campus for this announcement.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the class for this announcement.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the user who created this announcement.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active announcements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get urgent announcements.
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Scope to get announcements by scope.
     */
    public function scopeByScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Scope to get announcements by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get announcements by school.
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to get announcements by campus.
     */
    public function scopeByCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    /**
     * Scope to get announcements by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('publish_date', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now());
                    });
    }

    /**
     * Check if announcement is currently active.
     */
    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->publish_date && $this->publish_date->isFuture()) {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        return true;
    }
} 
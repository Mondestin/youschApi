<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;

class SchoolAdmin extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'campus_id',
        'user_id',
        'role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const ROLE_SCHOOL_ADMIN = 'school_admin';
    const ROLE_CAMPUS_ADMIN = 'campus_admin';

    /**
     * Get the school for this admin.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the campus for this admin (if campus-specific).
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the user who is this admin.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active admins.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get admins by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get school-level admins.
     */
    public function scopeSchoolAdmins($query)
    {
        return $query->where('role', self::ROLE_SCHOOL_ADMIN);
    }

    /**
     * Scope to get campus-level admins.
     */
    public function scopeCampusAdmins($query)
    {
        return $query->where('role', self::ROLE_CAMPUS_ADMIN);
    }

    /**
     * Scope to get admins by school.
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to get admins by campus.
     */
    public function scopeByCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }
} 
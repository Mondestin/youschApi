<?php

namespace App\Models\AdminAcademics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\Faculty;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\GradingScheme;
use App\Models\AdminAcademics\SchoolAdmin;
use App\Models\AdminAcademics\SchoolCalendar;
use App\Models\AdminAcademics\Announcement;

class School extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'domain',
        'contact_info',
        'address',
        'phone',
        'email',
        'website',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the campuses for this school.
     */
    public function campuses(): HasMany
    {
        return $this->hasMany(Campus::class);
    }

    /**
     * Get the faculties for this school.
     */
    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    /**
     * Get the academic years for this school.
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * Get the grading schemes for this school.
     */
    public function gradingSchemes(): HasMany
    {
        return $this->hasMany(GradingScheme::class);
    }

    /**
     * Get the school admins for this school.
     */
    public function schoolAdmins(): HasMany
    {
        return $this->hasMany(SchoolAdmin::class);
    }

    /**
     * Get the school calendar events for this school.
     */
    public function calendarEvents(): HasMany
    {
        return $this->hasMany(SchoolCalendar::class);
    }

    /**
     * Get the announcements for this school.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
} 
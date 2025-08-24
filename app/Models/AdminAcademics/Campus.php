<?php

namespace App\Models\AdminAcademics;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\SchoolAdmin;
use App\Models\AdminAcademics\Announcement;

class Campus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'address',
        'phone',
        'email',
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
     * Get the school that owns this campus.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the classes for this campus.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'campus_id');
    }

    /**
     * Get the school admins for this campus.
     */
    public function schoolAdmins(): HasMany
    {
        return $this->hasMany(SchoolAdmin::class, 'campus_id');
    }

    /**
     * Get the announcements for this campus.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'campus_id');
    }
} 
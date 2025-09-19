<?php

namespace App\Models\Teachers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Lab;
use App\Models\AdminAcademics\Venue;

class TeacherTimetable extends Model
{
    use HasFactory;

    protected $table = 'timetables';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'lab_id',
        'date',
        'start_time',
        'end_time',
        'venue_id',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the teacher for this timetable entry.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class for this timetable entry.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the subject for this timetable entry.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the lab for this timetable entry.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the venue for this timetable entry.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the duration of the session in minutes.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Check if timetable entry is for today.
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Check if timetable entry is for a specific day of week.
     */
    public function isDayOfWeek(int $dayOfWeek): bool
    {
        return $this->date->dayOfWeek === $dayOfWeek;
    }

    /**
     * Scope query to timetables by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query to timetables by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query to timetables by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope query to timetables by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope query to timetables by time range.
     */
    public function scopeByTimeRange($query, $startTime, $endTime)
    {
        return $query->whereBetween('start_time', [$startTime, $endTime]);
    }
} 
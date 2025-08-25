<?php

namespace App\Models\Students;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'document_type',
        'document_path',
        'original_filename',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // Document type constants
    const TYPE_BIRTH_CERTIFICATE = 'Birth Certificate';
    const TYPE_TRANSCRIPT = 'Transcript';
    const TYPE_ID_CARD = 'ID Card';
    const TYPE_MEDICAL_CERTIFICATE = 'Medical Certificate';
    const TYPE_TRANSFER_CERTIFICATE = 'Transfer Certificate';
    const TYPE_CHARACTER_CERTIFICATE = 'Character Certificate';
    const TYPE_OTHER = 'Other';

    /**
     * Get the student for this document.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Scope to get documents by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope to get documents by student.
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get documents uploaded today.
     */
    public function scopeUploadedToday(Builder $query): Builder
    {
        return $query->whereDate('uploaded_at', today());
    }

    /**
     * Scope to get documents uploaded this week.
     */
    public function scopeUploadedThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('uploaded_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope to get documents uploaded this month.
     */
    public function scopeUploadedThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('uploaded_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Check if document is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if document is a document file.
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file extension.
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Get the document URL.
     */
    public function getDocumentUrlAttribute(): string
    {
        return asset('storage/' . $this->document_path);
    }
} 
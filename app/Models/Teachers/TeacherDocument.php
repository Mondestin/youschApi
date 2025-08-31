<?php

namespace App\Models\Teachers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDocument extends Model
{
    use HasFactory;

    protected $table = 'teacher_documents';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'document_type',
        'document_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Document type constants
    const TYPE_CV = 'CV';
    const TYPE_CERTIFICATE = 'Certificate';
    const TYPE_CONTRACT = 'Contract';
    const TYPE_ID_PROOF = 'ID Proof';
    const TYPE_ACADEMIC_QUALIFICATION = 'Academic Qualification';
    const TYPE_OTHER = 'Other';

    /**
     * Get the teacher that owns the document.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the document URL.
     */
    public function getDocumentUrlAttribute(): string
    {
        return asset('storage/' . $this->document_path);
    }

    /**
     * Get the document file name.
     */
    public function getFileNameAttribute(): string
    {
        return basename($this->document_path);
    }

    /**
     * Get the document file extension.
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->document_path, PATHINFO_EXTENSION);
    }

    /**
     * Check if document is an image.
     */
    public function isImage(): bool
    {
        return in_array(strtolower($this->file_extension), ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Check if document is a PDF.
     */
    public function isPdf(): bool
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    /**
     * Check if document is a Word document.
     */
    public function isWordDocument(): bool
    {
        return in_array(strtolower($this->file_extension), ['doc', 'docx']);
    }

    /**
     * Scope query to documents by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope query to documents by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query to recent documents.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('uploaded_at', '>=', now()->subDays($days));
    }
} 
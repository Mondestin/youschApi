<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Students Management Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Students Management Service
    | including rate limiting, file upload settings, and other configurations.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for the students API endpoints.
    |
    */
    'api' => [
        'prefix' => env('STUDENTS_API_PREFIX', 'api/admin/students'),
        'version' => env('STUDENTS_API_VERSION', 'v1'),
        'rate_limit' => [
            'general' => env('STUDENTS_API_RATE_LIMIT', 120), // requests per minute
            'analytics' => env('STUDENTS_API_ANALYTICS_RATE_LIMIT', 30), // requests per minute
        ],
        'pagination' => [
            'default_per_page' => env('STUDENTS_API_DEFAULT_PER_PAGE', 15),
            'max_per_page' => env('STUDENTS_API_MAX_PER_PAGE', 100),
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload settings for student documents.
    |
    */
    'file_upload' => [
        'max_size' => env('STUDENTS_MAX_FILE_SIZE', 10240), // 10MB in KB
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'other' => ['txt', 'rtf'],
        ],
        'storage_path' => env('STUDENTS_STORAGE_PATH', 'students/documents'),
        'disk' => env('STUDENTS_STORAGE_DISK', 'public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Student Number Generation
    |--------------------------------------------------------------------------
    |
    | Configure student number generation settings.
    |
    */
    'student_number' => [
        'prefix' => env('STUDENT_NUMBER_PREFIX', 'STU'),
        'year_format' => env('STUDENT_NUMBER_YEAR_FORMAT', 'Y'),
        'sequence_length' => env('STUDENT_NUMBER_SEQUENCE_LENGTH', 4),
        'separator' => env('STUDENT_NUMBER_SEPARATOR', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Academic Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure academic performance calculation settings.
    |
    */
    'academic_performance' => [
        'gpa_scale' => [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F' => 0.0
        ],
        'passing_grade' => env('STUDENTS_PASSING_GRADE', 'D-'),
        'honor_roll_gpa' => env('STUDENTS_HONOR_ROLL_GPA', 3.5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification settings for student-related events.
    |
    */
    'notifications' => [
        'enabled' => env('STUDENTS_NOTIFICATIONS_ENABLED', true),
        'channels' => [
            'email' => env('STUDENTS_EMAIL_NOTIFICATIONS', true),
            'sms' => env('STUDENTS_SMS_NOTIFICATIONS', false),
            'push' => env('STUDENTS_PUSH_NOTIFICATIONS', false),
        ],
        'events' => [
            'application_submitted' => true,
            'application_approved' => true,
            'application_rejected' => true,
            'student_enrolled' => true,
            'student_graduated' => true,
            'student_transferred' => true,
            'academic_warning' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure pagination settings for API responses.
    |
    */
    'pagination' => [
        'default_per_page' => env('STUDENTS_DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('STUDENTS_MAX_PER_PAGE', 100),
        'available_per_page' => [10, 15, 25, 50, 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure cache settings for frequently accessed data.
    |
    */
    'cache' => [
        'enabled' => env('STUDENTS_CACHE_ENABLED', true),
        'ttl' => [
            'statistics' => env('STUDENTS_CACHE_TTL_STATISTICS', 3600), // 1 hour
            'academic_performance' => env('STUDENTS_CACHE_TTL_ACADEMIC', 1800), // 30 minutes
            'student_list' => env('STUDENTS_CACHE_TTL_LIST', 900), // 15 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Configure export settings for bulk operations.
    |
    */
    'export' => [
        'formats' => ['csv', 'xlsx', 'pdf'],
        'max_records' => env('STUDENTS_EXPORT_MAX_RECORDS', 10000),
        'chunk_size' => env('STUDENTS_EXPORT_CHUNK_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Configure validation rules for student data.
    |
    */
    'validation' => [
        'student' => [
            'min_age' => env('STUDENTS_MIN_AGE', 3),
            'max_age' => env('STUDENTS_MAX_AGE', 25),
            'phone_format' => env('STUDENTS_PHONE_FORMAT', '/^\+?[1-9]\d{1,14}$/'),
        ],
        'document' => [
            'max_file_size' => env('STUDENTS_DOC_MAX_SIZE', 10240), // 10MB in KB
            'allowed_mime_types' => [
                'image/jpeg', 'image/png', 'image/gif',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain', 'text/rtf'
            ],
        ],
    ],
]; 
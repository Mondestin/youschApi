<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Teacher Management Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Teacher Management Service
    | including rate limiting, pagination, and other service-specific settings.
    |
    */

    'rate_limit' => [
        'per_minute' => env('TEACHERS_API_RATE_LIMIT', 60),
        'per_hour' => env('TEACHERS_API_RATE_LIMIT_HOUR', 1000),
    ],

    'pagination' => [
        'default_per_page' => env('TEACHERS_PAGINATION_PER_PAGE', 15),
        'max_per_page' => env('TEACHERS_PAGINATION_MAX_PER_PAGE', 100),
    ],

    'file_upload' => [
        'max_size' => env('TEACHERS_FILE_MAX_SIZE', 10240), // 10MB in KB
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'storage_path' => 'teachers/documents',
    ],

    'statuses' => [
        'active' => 'active',
        'on_leave' => 'on_leave',
        'resigned' => 'resigned',
        'suspended' => 'suspended',
    ],

    'leave_types' => [
        'vacation' => 'vacation',
        'sick' => 'sick',
        'unpaid' => 'unpaid',
        'other' => 'other',
    ],

    'leave_statuses' => [
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
    ],
]; 
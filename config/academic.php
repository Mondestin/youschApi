<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Academic API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Academic Management API.
    |
    */

    'api' => [
        'prefix' => env('ACADEMIC_API_PREFIX', 'api/admin/academics'),
        'version' => env('ACADEMIC_API_VERSION', 'v1'),
        'rate_limit' => [
            'general' => env('ACADEMIC_API_RATE_LIMIT', 120), // requests per minute
            'analytics' => env('ACADEMIC_API_ANALYTICS_RATE_LIMIT', 30), // requests per minute
        ],
        'pagination' => [
            'default_per_page' => env('ACADEMIC_API_DEFAULT_PER_PAGE', 15),
            'max_per_page' => env('ACADEMIC_API_MAX_PER_PAGE', 100),
        ],
    ],

    'features' => [
        'bulk_operations' => env('ACADEMIC_BULK_OPERATIONS', true),
        'advanced_search' => env('ACADEMIC_ADVANCED_SEARCH', true),
        'export_functionality' => env('ACADEMIC_EXPORT_FUNCTIONALITY', true),
        'real_time_notifications' => env('ACADEMIC_REAL_TIME_NOTIFICATIONS', false),
    ],

    'cache' => [
        'enabled' => env('ACADEMIC_CACHE_ENABLED', true),
        'ttl' => env('ACADEMIC_CACHE_TTL', 3600), // seconds
        'prefix' => env('ACADEMIC_CACHE_PREFIX', 'academic'),
    ],

    'validation' => [
        'strict_mode' => env('ACADEMIC_STRICT_VALIDATION', false),
        'custom_rules' => env('ACADEMIC_CUSTOM_VALIDATION_RULES', []),
    ],

    'security' => [
        'require_authentication' => env('ACADEMIC_REQUIRE_AUTH', true),
        'require_authorization' => env('ACADEMIC_REQUIRE_AUTHORIZATION', true),
        'audit_logging' => env('ACADEMIC_AUDIT_LOGGING', true),
    ],
]; 
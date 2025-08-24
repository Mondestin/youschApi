# Academic Management API Route Architecture

## Overview

This document describes the route architecture for the Academic Management API, which follows Laravel and OOP best practices for production-ready applications.

## Architecture Components

### 1. Route Service Providers

#### ApiRouteServiceProvider (`app/Providers/ApiRouteServiceProvider.php`)
- **Purpose**: Main API route provider that handles general API routes
- **Features**:
  - Configures rate limiting for general API endpoints
  - Loads basic API routes (`/api/health`, `/api/version`, `/api/user`)
  - Applies `api` middleware group

#### AcademicRouteServiceProvider (`app/Providers/AcademicRouteServiceProvider.php`)
- **Purpose**: Dedicated provider for academic management routes
- **Features**:
  - Loads academic-specific routes under `/api/admin/academics`
  - Applies authentication (`auth:sanctum`) and rate limiting middleware
  - Uses the `AcademicRouteService` for route registration

### 2. Service Layer

#### AcademicRouteService (`app/Services/AcademicRouteService.php`)
- **Purpose**: Service class that encapsulates route registration logic
- **Features**:
  - Follows Single Responsibility Principle
  - Organizes routes by feature category
  - Provides clean, maintainable route structure
  - Uses static methods for easy access

### 3. Middleware

#### AcademicApiRateLimit (`app/Http/Middleware/AcademicApiRateLimit.php`)
- **Purpose**: Rate limiting middleware specific to academic endpoints
- **Features**:
  - Different rate limits for general vs. analytics endpoints
  - User-based and IP-based rate limiting
  - Configurable limits (120/min for general, 30/min for analytics)

### 4. Configuration

#### Academic Config (`config/academic.php`)
- **Purpose**: Centralized configuration for academic API settings
- **Features**:
  - Environment-based configuration
  - Rate limiting settings
  - Feature flags
  - Cache configuration
  - Security settings

## Route Structure

```
/api/admin/academics/
├── schools/                    # School management
├── campuses/                   # Campus management
├── academic-years/            # Academic year setup
├── terms/                     # Term management
├── faculties/                 # Faculty management
├── departments/               # Department management
├── courses/                   # Course management
├── subjects/                  # Subject management
├── classes/                   # Class management
├── timetables/               # Timetable management
├── exams/                     # Exam management
├── grading-schemes/          # Grading scheme management
├── enrollments/              # Student enrollment
├── grades/                    # Student grades
├── teacher-assignments/      # Teacher assignments
├── announcements/            # Announcements
└── analytics/                # Analytics and reports
```

## Benefits of This Architecture

### 1. **Separation of Concerns**
- Route registration logic is separated from providers
- Each provider has a single responsibility
- Service layer handles business logic

### 2. **Maintainability**
- Easy to add new route categories
- Clear organization by feature
- Consistent naming conventions

### 3. **Scalability**
- Modular structure allows easy expansion
- Rate limiting can be configured per endpoint type
- Configuration is environment-based

### 4. **Security**
- Authentication required for all academic endpoints
- Rate limiting prevents abuse
- Middleware can be easily extended

### 5. **Testing**
- Service methods can be unit tested
- Providers can be mocked
- Clear separation makes testing easier

## Usage Examples

### Adding New Routes
```php
// In AcademicRouteService
private static function registerNewFeatureRoutes(): void
{
    Route::prefix('new-feature')->name('new-feature.')->group(function () {
        Route::get('/', [NewFeatureController::class, 'index'])->name('index');
        Route::post('/', [NewFeatureController::class, 'store'])->name('store');
    });
}

// Call in registerRoutes method
public static function registerRoutes(): void
{
    // ... existing routes
    self::registerNewFeatureRoutes();
}
```

### Custom Rate Limiting
```php
// In AcademicApiRateLimit middleware
if (str_contains($request->path(), 'new-feature')) {
    if (RateLimiter::tooManyAttempts('new-feature-api:' . $key, 50)) {
        // Custom rate limit for new feature
    }
}
```

## Configuration

### Environment Variables
```env
ACADEMIC_API_PREFIX=api/admin/academics
ACADEMIC_API_VERSION=v1
ACADEMIC_API_RATE_LIMIT=120
ACADEMIC_API_ANALYTICS_RATE_LIMIT=30
ACADEMIC_CACHE_ENABLED=true
ACADEMIC_STRICT_VALIDATION=false
```

### Cache Configuration
```php
// In AcademicRouteService
Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->middleware('cache:academic,3600');
```

## Best Practices Followed

1. **SOLID Principles**: Single Responsibility, Open/Closed, etc.
2. **Laravel Conventions**: Follows Laravel 11 best practices
3. **Middleware Pattern**: Proper use of middleware for cross-cutting concerns
4. **Service Layer**: Business logic separated from route definitions
5. **Configuration Management**: Environment-based configuration
6. **Rate Limiting**: Proper API protection
7. **Authentication**: Secure by default
8. **Documentation**: Clear documentation and examples

## Future Enhancements

1. **API Versioning**: Support for multiple API versions
2. **GraphQL Support**: Optional GraphQL endpoint
3. **WebSocket Integration**: Real-time notifications
4. **Advanced Caching**: Redis-based caching strategies
5. **API Analytics**: Usage tracking and monitoring
6. **Rate Limit Profiles**: Different limits for different user types
7. **Route Validation**: Automatic route validation
8. **Performance Monitoring**: Built-in performance metrics 
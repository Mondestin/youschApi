# Attendance Management System

## Overview

The Attendance Management System is a comprehensive solution for tracking student and teacher attendance, managing excuse requests, and generating detailed reports. It provides real-time attendance monitoring, automated excuse processing, and comprehensive analytics for educational institutions.

## Features

### 🎯 **Core Functionality**
- **Student Attendance Tracking**: Daily attendance per class, subject, and lab session
- **Teacher Attendance Tracking**: Monitor teacher presence and punctuality
- **Excuse Management**: Handle student and teacher excuse requests with approval workflow
- **Real-time Dashboard**: Live attendance status and statistics
- **Bulk Operations**: Import/export attendance data in bulk
- **Comprehensive Reporting**: Generate detailed attendance and excuse reports

### 📊 **Attendance Statuses**
- **Students**: Present, Absent, Late, Excused
- **Teachers**: Present, Absent, Late

### 🔄 **Excuse Workflow**
- **Pending**: Awaiting review
- **Approved**: Excuse accepted, attendance updated
- **Rejected**: Excuse denied, attendance remains marked as absent

## Architecture

### **Database Structure**
```
├── student_attendance           # Student daily attendance records
├── teacher_attendance          # Teacher daily attendance records
├── student_attendance_excuses  # Student excuse requests
└── teacher_attendance_excuses # Teacher excuse requests
```

### **Service Layers**
```
├── Models/Attendance/          # Eloquent models with relationships
├── Repositories/Attendance/    # Data access layer with business logic
├── Controllers/Api/Attendance/ # API controllers for HTTP requests
├── Services/                   # Route service for API endpoints
└── Providers/                  # Service providers for dependency injection
```

## API Endpoints

### **Student Attendance**

#### **CRUD Operations**
```
GET    /api/attendance/student-attendance           # List all student attendance
POST   /api/attendance/student-attendance           # Create attendance record
GET    /api/attendance/student-attendance/{id}      # Get specific record
PUT    /api/attendance/student-attendance/{id}      # Update record
DELETE /api/attendance/student-attendance/{id}      # Delete record
```

#### **Filtered Queries**
```
GET    /api/attendance/student-attendance/student/{studentId}    # By student
GET    /api/attendance/student-attendance/class/{classId}        # By class
GET    /api/attendance/student-attendance/subject/{subjectId}    # By subject
GET    /api/attendance/student-attendance/date/{date}            # By date
POST   /api/attendance/student-attendance/date-range             # By date range
```

#### **Analytics & Reports**
```
GET    /api/attendance/student-attendance/statistics             # Attendance statistics
GET    /api/attendance/student-attendance/report                 # Detailed report
GET    /api/attendance/student-attendance/class/{id}/summary     # Class summary
GET    /api/attendance/student-attendance/student/{id}/summary   # Student summary
GET    /api/attendance/student-attendance/student/{id}/trends    # Student trends
```

### **Teacher Attendance**

#### **CRUD Operations**
```
GET    /api/attendance/teacher-attendance           # List all teacher attendance
POST   /api/attendance/teacher-attendance           # Create attendance record
GET    /api/attendance/teacher-attendance/{id}      # Get specific record
PUT    /api/attendance/teacher-attendance/{id}      # Update record
DELETE /api/attendance/teacher-attendance/{id}      # Delete record
```

#### **Filtered Queries**
```
GET    /api/attendance/teacher-attendance/teacher/{teacherId}    # By teacher
GET    /api/attendance/teacher-attendance/class/{classId}        # By class
GET    /api/attendance/teacher-attendance/subject/{subjectId}    # By subject
GET    /api/attendance/teacher-attendance/date/{date}            # By date
POST   /api/attendance/teacher-attendance/date-range             # By date range
```

#### **Analytics & Reports**
```
GET    /api/attendance/teacher-attendance/statistics             # Attendance statistics
GET    /api/attendance/teacher-attendance/report                 # Detailed report
GET    /api/attendance/teacher-attendance/class/{id}/summary     # Class summary
GET    /api/attendance/teacher-attendance/teacher/{id}/summary   # Teacher summary
GET    /api/attendance/teacher-attendance/teacher/{id}/trends    # Teacher trends
```

### **Student Excuses**

#### **CRUD Operations**
```
GET    /api/attendance/student-excuses              # List all excuses
POST   /api/attendance/student-excuses              # Create excuse request
GET    /api/attendance/student-excuses/{id}         # Get specific excuse
PUT    /api/attendance/student-excuses/{id}         # Update excuse
DELETE /api/attendance/student-excuses/{id}         # Delete excuse
```

#### **Filtered Queries**
```
GET    /api/attendance/student-excuses/student/{studentId}       # By student
GET    /api/attendance/student-excuses/class/{classId}           # By class
GET    /api/attendance/student-excuses/subject/{subjectId}       # By subject
GET    /api/attendance/student-excuses/date/{date}               # By date
POST   /api/attendance/student-excuses/date-range                # By date range
```

#### **Status-based Queries**
```
GET    /api/attendance/student-excuses/pending                   # Pending excuses
GET    /api/attendance/student-excuses/approved                  # Approved excuses
GET    /api/attendance/student-excuses/rejected                  # Rejected excuses
```

#### **Approval Actions**
```
POST   /api/attendance/student-excuses/{id}/approve              # Approve excuse
POST   /api/attendance/student-excuses/{id}/reject               # Reject excuse
```

#### **Analytics & Reports**
```
GET    /api/attendance/student-excuses/statistics                # Excuse statistics
GET    /api/attendance/student-excuses/report                    # Detailed report
GET    /api/attendance/student-excuses/student/{id}/trends       # Student trends
```

### **Teacher Excuses**

#### **CRUD Operations**
```
GET    /api/attendance/teacher-excuses              # List all excuses
POST   /api/attendance/teacher-excuses              # Create excuse request
GET    /api/attendance/teacher-excuses/{id}         # Get specific excuse
PUT    /api/attendance/teacher-excuses/{id}         # Update excuse
DELETE /api/attendance/teacher-excuses/{id}         # Delete excuse
```

#### **Filtered Queries**
```
GET    /api/attendance/teacher-excuses/teacher/{teacherId}       # By teacher
GET    /api/attendance/teacher-excuses/class/{classId}           # By class
GET    /api/attendance/teacher-excuses/subject/{subjectId}       # By subject
GET    /api/attendance/teacher-excuses/date/{date}               # By date
POST   /api/attendance/teacher-excuses/date-range                # By date range
```

#### **Status-based Queries**
```
GET    /api/attendance/teacher-excuses/pending                   # Pending excuses
GET    /api/attendance/teacher-excuses/approved                  # Approved excuses
GET    /api/attendance/teacher-excuses/rejected                  # Rejected excuses
```

#### **Approval Actions**
```
POST   /api/attendance/teacher-excuses/{id}/approve              # Approve excuse
POST   /api/attendance/teacher-excuses/{id}/reject               # Reject excuse
```

#### **Analytics & Reports**
```
GET    /api/attendance/teacher-excuses/statistics                # Excuse statistics
GET    /api/attendance/teacher-excuses/report                    # Detailed report
GET    /api/attendance/teacher-excuses/teacher/{id}/trends       # Teacher trends
```

### **Bulk Operations**
```
POST   /api/attendance/bulk/student-attendance/create            # Bulk create student attendance
POST   /api/attendance/bulk/student-attendance/update            # Bulk update student attendance
POST   /api/attendance/bulk/teacher-attendance/create            # Bulk create teacher attendance
POST   /api/attendance/bulk/teacher-attendance/update            # Bulk update teacher attendance
```

### **Reports & Statistics**
```
GET    /api/attendance/reports/student-attendance                # Student attendance report
GET    /api/attendance/reports/teacher-attendance                # Teacher attendance report
GET    /api/attendance/reports/student-excuses                   # Student excuses report
GET    /api/attendance/reports/teacher-excuses                   # Teacher excuses report
GET    /api/attendance/reports/student-attendance/stats          # Student attendance stats
GET    /api/attendance/reports/teacher-attendance/stats          # Teacher attendance stats
GET    /api/attendance/reports/student-excuses/stats             # Student excuses stats
GET    /api/attendance/reports/teacher-excuses/stats             # Teacher excuses stats
```

## Data Models

### **StudentAttendance**
```php
class StudentAttendance extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'subject_id', 'lab_id',
        'timetable_id', 'date', 'status', 'remarks'
    ];
    
    protected $casts = ['date' => 'date'];
    
    // Relationships
    public function student() { /* ... */ }
    public function class() { /* ... */ }
    public function subject() { /* ... */ }
    public function lab() { /* ... */ }
    public function timetable() { /* ... */ }
}
```

### **TeacherAttendance**
```php
class TeacherAttendance extends Model
{
    protected $fillable = [
        'teacher_id', 'class_id', 'subject_id', 'lab_id',
        'timetable_id', 'date', 'status', 'remarks'
    ];
    
    protected $casts = ['date' => 'date'];
    
    // Relationships
    public function teacher() { /* ... */ }
    public function class() { /* ... */ }
    public function subject() { /* ... */ }
    public function lab() { /* ... */ }
    public function timetable() { /* ... */ }
}
```

### **StudentAttendanceExcuse**
```php
class StudentAttendanceExcuse extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'subject_id', 'lab_id',
        'date', 'reason', 'document_path', 'status',
        'reviewed_by', 'reviewed_on'
    ];
    
    protected $casts = [
        'date' => 'date',
        'reviewed_on' => 'datetime'
    ];
    
    // Relationships
    public function student() { /* ... */ }
    public function class() { /* ... */ }
    public function subject() { /* ... */ }
    public function lab() { /* ... */ }
    public function reviewer() { /* ... */ }
}
```

### **TeacherAttendanceExcuse**
```php
class TeacherAttendanceExcuse extends Model
{
    protected $fillable = [
        'teacher_id', 'class_id', 'subject_id', 'lab_id',
        'date', 'reason', 'document_path', 'status',
        'reviewed_by', 'reviewed_on'
    ];
    
    protected $casts = [
        'date' => 'date',
        'reviewed_on' => 'datetime'
    ];
    
    // Relationships
    public function teacher() { /* ... */ }
    public function class() { /* ... */ }
    public function subject() { /* ... */ }
    public function lab() { /* ... */ }
    public function reviewer() { /* ... */ }
}
```

## Business Logic

### **Attendance Recording**
1. **Manual Entry**: Teachers can manually record attendance
2. **Bulk Import**: CSV/Excel files can be imported for multiple records
3. **Real-time Updates**: Attendance status updates immediately
4. **Conflict Resolution**: Prevents duplicate attendance records

### **Excuse Processing**
1. **Request Submission**: Students/teachers submit excuse requests
2. **Document Upload**: Supporting documents can be attached
3. **Review Process**: Administrators review and approve/reject
4. **Auto-update**: Approved excuses automatically update attendance

### **Statistics & Analytics**
1. **Attendance Rates**: Calculate present/absent percentages
2. **Trend Analysis**: Track attendance patterns over time
3. **Class Performance**: Compare attendance across classes
4. **Individual Tracking**: Monitor student/teacher attendance history

## Usage Examples

### **Create Student Attendance**
```php
// Controller method
public function store(Request $request)
{
    $attendance = $this->attendanceRepository->createAttendance([
        'student_id' => $request->student_id,
        'class_id' => $request->class_id,
        'subject_id' => $request->subject_id,
        'timetable_id' => $request->timetable_id,
        'date' => $request->date,
        'status' => 'present',
        'remarks' => 'On time'
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $attendance
    ]);
}
```

### **Bulk Create Attendance**
```php
// Controller method
public function bulkCreate(Request $request)
{
    $results = $this->attendanceRepository->bulkCreateAttendance(
        $request->attendance_data
    );
    
    return response()->json([
        'success' => true,
        'data' => $results
    ]);
}
```

### **Get Attendance Statistics**
```php
// Controller method
public function statistics(Request $request)
{
    $statistics = $this->attendanceRepository->getAttendanceStatistics([
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'class_id' => $request->class_id
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $statistics
    ]);
}
```

### **Approve Excuse Request**
```php
// Controller method
public function approve(StudentAttendanceExcuse $excuse)
{
    $reviewerId = Auth::id();
    
    $approved = $this->excuseRepository->approveExcuse(
        $excuse->id, 
        $reviewerId
    );
    
    if ($approved) {
        return response()->json([
            'success' => true,
            'message' => 'Excuse approved successfully'
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Failed to approve excuse'
    ], 500);
}
```

## Database Seeding

### **Run Seeder**
```bash
php artisan db:seed --class=AttendanceManagementSeeder
```

### **Seeder Features**
- **Realistic Data**: Generates attendance records for the past 30 days
- **Weighted Distribution**: Realistic attendance patterns (70% present, 15% absent, etc.)
- **Weekend Handling**: Skips weekends for realistic school schedules
- **Bulk Insertion**: Uses chunked insertion for memory efficiency
- **Relationship Handling**: Properly links to existing students, teachers, classes, and subjects

## Configuration

### **Service Provider Registration**
The `AttendanceRouteServiceProvider` is automatically registered in `bootstrap/app.php`:

```php
->withProviders([
    // ... other providers
    App\Providers\AttendanceRouteServiceProvider::class,
])
```

### **Repository Bindings**
All attendance repositories are bound in `RepositoryServiceProvider`:

```php
// Bind Attendance Repository Interfaces
$this->app->bind(StudentAttendanceRepositoryInterface::class, StudentAttendanceRepository::class);
$this->app->bind(TeacherAttendanceRepositoryInterface::class, TeacherAttendanceRepository::class);
$this->app->bind(StudentAttendanceExcuseRepositoryInterface::class, StudentAttendanceExcuseRepository::class);
$this->app->bind(TeacherAttendanceExcuseRepositoryInterface::class, TeacherAttendanceExcuseRepository::class);
```

## API Response Format

### **Success Response**
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully"
}
```

### **Error Response**
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

### **Pagination Response**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [...],
        "first_page_url": "...",
        "from": 1,
        "last_page": 5,
        "last_page_url": "...",
        "next_page_url": "...",
        "path": "...",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 75
    }
}
```

## Error Handling

### **Validation Errors**
- **Input Validation**: Comprehensive validation for all inputs
- **Business Rules**: Enforces attendance and excuse business logic
- **Error Messages**: Clear, user-friendly error descriptions

### **Database Errors**
- **Transaction Safety**: Bulk operations use database transactions
- **Rollback Support**: Automatic rollback on failure
- **Error Logging**: Comprehensive error logging for debugging

### **API Errors**
- **HTTP Status Codes**: Proper HTTP status codes for different error types
- **Error Details**: Detailed error information for client handling
- **Consistent Format**: Standardized error response format

## Performance Considerations

### **Database Optimization**
- **Indexes**: Strategic database indexes for common queries
- **Query Optimization**: Efficient Eloquent queries with proper relationships
- **Chunked Operations**: Memory-efficient bulk operations

### **Caching Strategy**
- **Query Caching**: Cache frequently accessed data
- **Result Caching**: Cache computed statistics and reports
- **Cache Invalidation**: Smart cache invalidation on data updates

### **API Performance**
- **Pagination**: Efficient pagination for large datasets
- **Filtering**: Optimized filtering and search capabilities
- **Response Optimization**: Minimized response payload sizes

## Security Features

### **Authentication & Authorization**
- **API Middleware**: Secure API endpoints with authentication
- **Role-based Access**: Different access levels for different user types
- **Input Sanitization**: Protection against malicious input

### **Data Validation**
- **Request Validation**: Comprehensive input validation
- **Business Rule Enforcement**: Prevents invalid data states
- **SQL Injection Protection**: Eloquent ORM protection

## Testing

### **Unit Tests**
- **Model Tests**: Test model relationships and methods
- **Repository Tests**: Test data access layer logic
- **Controller Tests**: Test API endpoint functionality

### **Integration Tests**
- **API Tests**: Test complete API workflows
- **Database Tests**: Test database operations and constraints
- **Business Logic Tests**: Test complex business scenarios

## Deployment

### **Requirements**
- **Laravel 10+**: Modern Laravel framework
- **PHP 8.1+**: Latest PHP version support
- **MySQL 8.0+**: Robust database support
- **Redis**: Optional caching support

### **Installation**
1. **Database Migration**: Run attendance table migrations
2. **Service Registration**: Ensure service providers are registered
3. **Route Registration**: Verify API routes are accessible
4. **Data Seeding**: Populate with sample data for testing

### **Environment Configuration**
```env
# Attendance Service Configuration
ATTENDANCE_CACHE_TTL=3600
ATTENDANCE_BULK_CHUNK_SIZE=1000
ATTENDANCE_MAX_FILE_SIZE=2048
ATTENDANCE_ALLOWED_FILE_TYPES=pdf,doc,docx,jpg,jpeg,png
```

## Support & Maintenance

### **Monitoring**
- **Performance Metrics**: Track API response times and throughput
- **Error Tracking**: Monitor and alert on system errors
- **Usage Analytics**: Track API usage patterns

### **Updates & Maintenance**
- **Regular Updates**: Keep dependencies up to date
- **Security Patches**: Apply security updates promptly
- **Performance Tuning**: Optimize based on usage patterns

### **Documentation**
- **API Documentation**: Comprehensive endpoint documentation
- **Code Comments**: Well-documented source code
- **Change Logs**: Track system changes and updates

## Conclusion

The Attendance Management System provides a robust, scalable solution for educational institutions to track attendance, manage excuses, and generate comprehensive reports. With its modern architecture, comprehensive API, and extensive features, it serves as a complete solution for attendance management needs.

For additional support or feature requests, please refer to the system documentation or contact the development team. 
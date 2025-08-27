# Exams & Gradings Management System

## Overview

The Exams & Gradings Management System is a comprehensive solution for managing academic examinations, grading, GPA calculation, and report card generation. It provides a complete workflow from exam scheduling to final grade reporting, integrating seamlessly with the existing Academic and Student Management services.

## Features

### 🎯 **Core Functionality**
- **Exam Type Management**: Define and configure different types of examinations with customizable weights
- **Exam Scheduling**: Schedule exams per subject, class, or lab session with conflict detection
- **Marks Entry**: Comprehensive system for entering and managing student exam marks
- **Grade Calculation**: Automatic grade calculation based on configurable grading schemes
- **GPA Management**: Term and cumulative GPA calculation with performance tracking
- **Report Card Generation**: Automated report card creation with PDF export capability

### 📊 **Exam Management**
- **Exam Types**: Midterm, Final, Quiz, Assignment, Practical, Project, Lab Test, Presentation
- **Weighted Grading**: Configurable weight percentages for different exam types
- **Status Tracking**: Scheduled, Completed, Cancelled exam states
- **Conflict Prevention**: Time and resource conflict detection during scheduling

### 🎓 **Grading & Assessment**
- **Flexible Grading**: Support for both numerical marks and letter grades
- **Grade Schemes**: Configurable grade boundaries and point systems
- **Performance Analytics**: Comprehensive performance tracking and analysis
- **Bulk Operations**: Import/export capabilities for large-scale grading

### 📈 **Academic Performance**
- **GPA Calculation**: Term-based and cumulative GPA computation
- **Performance Trends**: Track student progress across terms and academic years
- **Comparative Analysis**: Class and subject performance comparisons
- **Academic History**: Complete academic record maintenance

### 📋 **Reporting System**
- **Report Cards**: Automated generation with customizable templates
- **Performance Reports**: Detailed analytics and performance summaries
- **Export Options**: PDF and digital format support
- **Trend Analysis**: Student performance progression tracking

## Architecture

### **Database Structure**
```
├── exam_types              # Exam type definitions with weights
├── exams                   # Exam scheduling and details
├── exam_marks             # Student exam results and grades
├── student_gpa            # Term and cumulative GPA records
└── report_cards           # Generated report card data
```

### **Service Layers**
```
├── Models/ExamsGradings/          # Eloquent models with relationships
├── Repositories/ExamsGradings/    # Data access layer with business logic
├── Controllers/Api/ExamsGradings/ # API controllers for HTTP requests
├── Services/                      # Route service for API endpoints
└── Providers/                     # Service providers for dependency injection
```

## API Endpoints

### **Exam Types**

#### **CRUD Operations**
```
GET    /api/exams-gradings/exam-types           # List all exam types
POST   /api/exams-gradings/exam-types           # Create exam type
GET    /api/exams-gradings/exam-types/{id}      # Get specific exam type
PUT    /api/exams-gradings/exam-types/{id}      # Update exam type
DELETE /api/exams-gradings/exam-types/{id}      # Delete exam type
```

#### **Specialized Queries**
```
GET    /api/exams-gradings/exam-types/all       # Get all for dropdowns
GET    /api/exams-gradings/exam-types/weighted  # Get weighted exam types
GET    /api/exams-gradings/exam-types/statistics # Get exam type statistics
```

### **Exams**

#### **CRUD Operations**
```
GET    /api/exams-gradings/exams                # List all exams
POST   /api/exams-gradings/exams                # Create exam
GET    /api/exams-gradings/exams/{id}           # Get specific exam
PUT    /api/exams-gradings/exams/{id}           # Update exam
DELETE /api/exams-gradings/exams/{id}           # Delete exam
```

#### **Status-based Queries**
```
GET    /api/exams-gradings/exams/upcoming       # Get upcoming exams
GET    /api/exams-gradings/exams/past           # Get past exams
GET    /api/exams-gradings/exams/scheduled      # Get scheduled exams
GET    /api/exams-gradings/exams/completed      # Get completed exams
GET    /api/exams-gradings/exams/cancelled      # Get cancelled exams
```

#### **Filtered Queries**
```
GET    /api/exams-gradings/exams/class/{id}     # Get exams by class
GET    /api/exams-gradings/exams/subject/{id}   # Get exams by subject
GET    /api/exams-gradings/exams/examiner/{id}  # Get exams by examiner
GET    /api/exams-gradings/exams/type/{id}      # Get exams by type
POST   /api/exams-gradings/exams/date-range     # Get exams by date range
```

#### **Exam Management**
```
POST   /api/exams-gradings/exams/{id}/complete  # Mark exam as completed
POST   /api/exams-gradings/exams/{id}/cancel    # Mark exam as cancelled
GET    /api/exams-gradings/exams/statistics     # Get exam statistics
```

### **Exam Marks**

#### **CRUD Operations**
```
GET    /api/exams-gradings/exam-marks           # List all exam marks
POST   /api/exams-gradings/exam-marks           # Create exam mark
GET    /api/exams-gradings/exam-marks/{id}      # Get specific exam mark
PUT    /api/exams-gradings/exam-marks/{id}      # Update exam mark
DELETE /api/exams-gradings/exam-marks/{id}      # Delete exam mark
```

#### **Filtered Queries**
```
GET    /api/exams-gradings/exam-marks/exam/{id}           # Get marks by exam
GET    /api/exams-gradings/exam-marks/student/{id}        # Get marks by student
GET    /api/exams-gradings/exam-marks/exam/{id}/student/{id} # Get specific student mark
```

#### **Analytics & Reports**
```
GET    /api/exams-gradings/exam-marks/statistics          # Get mark statistics
GET    /api/exams-gradings/exam-marks/exam/{id}/results   # Get exam results
```

### **Student GPA**

#### **CRUD Operations**
```
GET    /api/exams-gradings/student-gpa          # List all GPA records
POST   /api/exams-gradings/student-gpa          # Create GPA record
GET    /api/exams-gradings/student-gpa/{id}     # Get specific GPA record
PUT    /api/exams-gradings/student-gpa/{id}     # Update GPA record
DELETE /api/exams-gradings/student-gpa/{id}     # Delete GPA record
```

#### **Filtered Queries**
```
GET    /api/exams-gradings/student-gpa/student/{id}       # Get GPA by student
GET    /api/exams-gradings/student-gpa/term/{id}          # Get GPA by term
GET    /api/exams-gradings/student-gpa/academic-year/{id} # Get GPA by academic year
```

#### **Calculations & Analytics**
```
GET    /api/exams-gradings/student-gpa/calculate-gpa      # Calculate term GPA
GET    /api/exams-gradings/student-gpa/calculate-cgpa     # Calculate cumulative GPA
GET    /api/exams-gradings/student-gpa/top-performers     # Get top performers
GET    /api/exams-gradings/student-gpa/low-performers     # Get low performers
GET    /api/exams-gradings/student-gpa/gpa-distribution   # Get GPA distribution
GET    /api/exams-gradings/student-gpa/statistics         # Get GPA statistics
```

### **Report Cards**

#### **CRUD Operations**
```
GET    /api/exams-gradings/report-cards         # List all report cards
POST   /api/exams-gradings/report-cards         # Create report card
GET    /api/exams-gradings/report-cards/{id}    # Get specific report card
PUT    /api/exams-gradings/report-cards/{id}    # Update report card
DELETE /api/exams-gradings/report-cards/{id}    # Delete report card
```

#### **Filtered Queries**
```
GET    /api/exams-gradings/report-cards/student/{id}      # Get by student
GET    /api/exams-gradings/report-cards/class/{id}        # Get by class
GET    /api/exams-gradings/report-cards/term/{id}         # Get by term
GET    /api/exams-gradings/report-cards/academic-year/{id} # Get by academic year
```

#### **Generation & Export**
```
POST   /api/exams-gradings/report-cards/generate          # Generate report card
POST   /api/exams-gradings/report-cards/generate-class    # Generate class report cards
GET    /api/exams-gradings/report-cards/{id}/export-pdf   # Export to PDF
```

#### **Analytics**
```
GET    /api/exams-gradings/report-cards/statistics        # Get report card statistics
GET    /api/exams-gradings/report-cards/student/{id}/trends # Get student trends
```

### **Bulk Operations**
```
POST   /api/exams-gradings/bulk/exam-marks/create         # Bulk create exam marks
POST   /api/exams-gradings/bulk/exam-marks/update         # Bulk update exam marks
POST   /api/exams-gradings/bulk/student-gpa/create        # Bulk create GPA records
POST   /api/exams-gradings/bulk/student-gpa/update        # Bulk update GPA records
POST   /api/exams-gradings/bulk/report-cards/generate     # Bulk generate report cards
```

### **Reports & Analytics**
```
GET    /api/exams-gradings/reports/exam-performance       # Exam performance report
GET    /api/exams-gradings/reports/student-gpa-analysis   # Student GPA analysis
GET    /api/exams-gradings/reports/report-card-summary    # Report card summary
GET    /api/exams-gradings/reports/academic-progress      # Academic progress report
GET    /api/exams-gradings/reports/class-performance      # Class performance report
GET    /api/exams-gradings/reports/subject-performance    # Subject performance report
```

## Data Models

### **ExamType**
```php
class ExamType extends Model
{
    protected $fillable = [
        'name', 'description', 'weight'
    ];
    
    protected $casts = ['weight' => 'decimal:2'];
    
    // Relationships
    public function exams() { /* ... */ }
    
    // Scopes
    public function scopeByWeight() { /* ... */ }
    public function scopeWithWeightGreaterThan() { /* ... */ }
    
    // Helper methods
    public function isWeighted() { /* ... */ }
    public function getWeightPercentageAttribute() { /* ... */ }
}
```

### **Exam**
```php
class Exam extends Model
{
    protected $fillable = [
        'class_id', 'subject_id', 'lab_id', 'exam_type_id',
        'exam_date', 'start_time', 'end_time', 'examiner_id',
        'instructions', 'status'
    ];
    
    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time'
    ];
    
    // Relationships
    public function classRoom() { /* ... */ }
    public function subject() { /* ... */ }
    public function lab() { /* ... */ }
    public function examType() { /* ... */ }
    public function examiner() { /* ... */ }
    public function examMarks() { /* ... */ }
    
    // Scopes
    public function scopeScheduled() { /* ... */ }
    public function scopeCompleted() { /* ... */ }
    public function scopeUpcoming() { /* ... */ }
    
    // Helper methods
    public function isScheduled() { /* ... */ }
    public function getDurationAttribute() { /* ... */ }
    public function getPassRateAttribute() { /* ... */ }
}
```

### **ExamMark**
```php
class ExamMark extends Model
{
    protected $fillable = [
        'exam_id', 'student_id', 'marks_obtained',
        'grade', 'remarks'
    ];
    
    protected $casts = ['marks_obtained' => 'decimal:2'];
    
    // Relationships
    public function exam() { /* ... */ }
    public function student() { /* ... */ }
    
    // Scopes
    public function scopeByExam() { /* ... */ }
    public function scopeByStudent() { /* ... */ }
    public function scopeWithGrades() { /* ... */ }
    
    // Helper methods
    public function isPassing() { /* ... */ }
    public function getPercentageAttribute() { /* ... */ }
    public function getGradeColorAttribute() { /* ... */ }
}
```

### **StudentGPA**
```php
class StudentGPA extends Model
{
    protected $fillable = [
        'student_id', 'term_id', 'academic_year_id',
        'gpa', 'cgpa'
    ];
    
    protected $casts = [
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2'
    ];
    
    // Relationships
    public function student() { /* ... */ }
    public function term() { /* ... */ }
    public function academicYear() { /* ... */ }
    
    // Scopes
    public function scopeByStudent() { /* ... */ }
    public function scopeByTerm() { /* ... */ }
    public function scopeHighPerformance() { /* ... */ }
    
    // Helper methods
    public function isExcellent() { /* ... */ }
    public function getGpaStatusAttribute() { /* ... */ }
    public function getGpaColorAttribute() { /* ... */ }
}
```

### **ReportCard**
```php
class ReportCard extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'term_id', 'academic_year_id',
        'gpa', 'cgpa', 'remarks', 'issued_date', 'format'
    ];
    
    protected $casts = [
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'issued_date' => 'date'
    ];
    
    // Relationships
    public function student() { /* ... */ }
    public function class() { /* ... */ }
    public function term() { /* ... */ }
    public function academicYear() { /* ... */ }
    
    // Scopes
    public function scopeByStudent() { /* ... */ }
    public function scopeByClass() { /* ... */ }
    public function scopeRecent() { /* ... */ }
    
    // Helper methods
    public function isPDF() { /* ... */ }
    public function hasExcellentPerformance() { /* ... */ }
    public function getPerformanceStatusAttribute() { /* ... */ }
}
```

## Business Logic

### **Exam Scheduling**
1. **Conflict Detection**: Prevents overlapping exams for same class/teacher
2. **Resource Allocation**: Manages lab and room assignments
3. **Time Validation**: Ensures logical start/end times
4. **Status Management**: Tracks exam lifecycle from scheduled to completed

### **Grade Calculation**
1. **Automatic Grading**: Converts marks to letter grades based on schemes
2. **Weighted Scoring**: Applies exam type weights to final calculations
3. **Grade Validation**: Ensures grades fall within acceptable ranges
4. **Performance Tracking**: Monitors student progress over time

### **GPA Computation**
1. **Term GPA**: Calculates GPA for specific academic terms
2. **Cumulative GPA**: Tracks overall academic performance
3. **Grade Point Conversion**: Maps letter grades to numerical points
4. **Performance Analysis**: Identifies trends and patterns

### **Report Card Generation**
1. **Data Aggregation**: Collects grades, GPA, and attendance data
2. **Template Processing**: Applies customizable report templates
3. **PDF Generation**: Creates professional PDF reports
4. **Digital Distribution**: Supports online report card access

## Integration Points

### **With Academic Service**
- **Class Management**: Links exams to specific classes and subjects
- **Timetable Integration**: Coordinates with existing class schedules
- **Academic Structure**: Respects terms and academic years

### **With Student Service**
- **Student Records**: Accesses student information and enrollment data
- **Academic History**: Updates student academic records
- **Performance Tracking**: Maintains comprehensive student profiles

### **With Teacher Service**
- **Examiner Assignment**: Links exams to responsible teachers
- **Grade Entry**: Provides interface for teacher mark entry
- **Performance Review**: Tracks teacher grading patterns

## Configuration

### **Service Provider Registration**
The `ExamsGradingsRouteServiceProvider` is automatically registered in `bootstrap/app.php`:

```php
->withProviders([
    // ... other providers
    App\Providers\ExamsGradingsRouteServiceProvider::class,
])
```

### **Repository Bindings**
All ExamsGradings repositories are bound in `RepositoryServiceProvider`:

```php
// Bind Exams & Gradings Repository Interfaces
$this->app->bind(ExamTypeRepositoryInterface::class, ExamTypeRepository::class);
$this->app->bind(ExamMarkRepositoryInterface::class, ExamMarkRepository::class);
$this->app->bind(StudentGPARepositoryInterface::class, StudentGPARepository::class);
$this->app->bind(ReportCardRepositoryInterface::class, ReportCardRepository::class);
```

## Database Seeding

### **Run Seeder**
```bash
php artisan db:seed --class=ExamsGradingsSeeder
```

### **Seeder Features**
- **Realistic Data**: Generates comprehensive exam and grading data
- **Performance Variation**: Creates realistic grade distributions
- **Relationship Handling**: Properly links all related entities
- **Bulk Insertion**: Uses efficient data insertion methods

## Usage Examples

### **Create Exam Type**
```php
// Controller method
public function store(Request $request)
{
    $examType = $this->examTypeRepository->createExamType([
        'name' => 'Midterm',
        'description' => 'Mid-semester examination',
        'weight' => 30.0
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $examType
    ]);
}
```

### **Schedule Exam**
```php
// Controller method
public function store(Request $request)
{
    $exam = $this->examRepository->createExam([
        'class_id' => $request->class_id,
        'subject_id' => $request->subject_id,
        'exam_type_id' => $request->exam_type_id,
        'exam_date' => $request->exam_date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'examiner_id' => $request->examiner_id,
        'instructions' => $request->instructions
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $exam
    ]);
}
```

### **Enter Exam Marks**
```php
// Controller method
public function store(Request $request)
{
    $examMark = $this->examMarkRepository->createExamMark([
        'exam_id' => $request->exam_id,
        'student_id' => $request->student_id,
        'marks_obtained' => $request->marks_obtained,
        'grade' => $this->calculateGrade($request->marks_obtained),
        'remarks' => $request->remarks
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $examMark
    ]);
}
```

### **Generate Report Card**
```php
// Controller method
public function generate(Request $request)
{
    $reportCard = $this->reportCardRepository->generateReportCard(
        $request->student_id,
        $request->class_id,
        $request->term_id,
        $request->academic_year_id
    );
    
    return response()->json([
        'success' => true,
        'data' => $reportCard
    ]);
}
```

## Error Handling

### **Validation Errors**
- **Input Validation**: Comprehensive validation for all inputs
- **Business Rules**: Enforces exam and grading business logic
- **Conflict Resolution**: Prevents scheduling and grading conflicts

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
- **PDF Generation**: For report card export functionality

### **Installation**
1. **Database Migration**: Run all ExamsGradings table migrations
2. **Service Registration**: Ensure service providers are registered
3. **Route Registration**: Verify API routes are accessible
4. **Data Seeding**: Populate with sample data for testing

## Support & Maintenance

### **Monitoring**
- **Performance Metrics**: Track API response times and throughput
- **Error Tracking**: Monitor and alert on system errors
- **Usage Analytics**: Track API usage patterns

### **Updates & Maintenance**
- **Regular Updates**: Keep dependencies up to date
- **Security Patches**: Apply security updates promptly
- **Performance Tuning**: Optimize based on usage patterns

## Conclusion

The Exams & Gradings Management System provides a comprehensive, scalable solution for educational institutions to manage the complete examination lifecycle. With its modern architecture, comprehensive API, and extensive features, it serves as a complete solution for exam management, grading, and academic reporting needs.

The system seamlessly integrates with existing Academic and Student Management services while providing new capabilities for exam scheduling, grade management, and performance analytics. Its modular design makes it easy to extend and customize for specific institutional requirements.

For additional support or feature requests, please refer to the system documentation or contact the development team. 
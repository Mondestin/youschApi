# Students Management Service

A comprehensive, production-ready Students Management Service built with Laravel 12, following OOP best practices and RESTful API design principles.

## 🚀 Features

### 1. Student Enrollment & Admissions
- **Online Application System**: Accept admission applications online or manually
- **Application Review**: Review and approve/reject applications with workflow management
- **Campus Assignment**: Assign students to campuses, faculties, departments, and classes
- **Transfer Management**: Handle transfer requests between classes or campuses
- **Waiting Lists**: Manage waiting lists and quotas for popular programs

### 2. Student Profiles
- **Personal Information**: Store name, gender, date of birth, contact info
- **Academic Information**: Track current class, program, course enrollment
- **Medical Information**: Store allergies, medical history, emergency contacts
- **Parent/Guardian Info**: Comprehensive parent contact and relationship management
- **Document Management**: Upload and manage student documents (ID, certificates, transcripts)

### 3. Class & Section Assignment
- **Dynamic Assignment**: Assign students to classes, sections, and lab groups
- **Reassignment**: Handle student reshuffling when classes are reorganized
- **Roll Number Management**: Track student roll numbers or IDs systematically

### 4. Academic History Tracking
- **Grade Management**: Record grades per subject, course, term, and year
- **Promotion/Retention**: Maintain promotion/retention history
- **Exam Tracking**: Track exams taken, scores, and GPA calculations
- **Transcript Generation**: Generate comprehensive student transcripts

### 5. Graduation & Transfer Management
- **Eligibility Verification**: Verify graduation eligibility based on academic requirements
- **Transfer Processing**: Process transfer requests between schools or campuses
- **Certificate Management**: Issue certificates of completion, transcripts, and diplomas

### 6. Attendance Integration (Optional Module)
- **Service Integration**: Link with Attendance Service for class-level attendance tracking
- **History Display**: Show attendance history in student profile

### 7. Notifications & Alerts
- **Status Updates**: Notify students about admission status, class assignment, and deadlines
- **Document Alerts**: Alerts for missing documents or incomplete profiles
- **Academic Warnings**: Notifications for academic performance issues

### 8. Analytics & Reports
- **Enrollment Statistics**: Per class, department, campus analysis
- **Dropout Tracking**: Monitor dropout rates and retention metrics
- **Demographics**: Student demographics (gender, age, nationality)
- **Progress Reports**: Academic progress reports per student, class, or department

## 🏗️ Architecture

### Database Schema
The service includes 6 core tables:

1. **`student_applications`** - Admission applications and review process
2. **`students`** - Core student information and profiles
3. **`academic_history`** - Academic performance records
4. **`student_transfers`** - Transfer requests and approvals
5. **`student_graduation`** - Graduation records and diplomas
6. **`student_documents`** - Document storage and management

### Models
- **`StudentApplication`** - Handles admission workflow
- **`Student`** - Core student entity with comprehensive relationships
- **`AcademicHistory`** - Academic performance tracking
- **`StudentTransfer`** - Transfer request management
- **`StudentGraduation`** - Graduation process management
- **`StudentDocument`** - Document storage and retrieval

### Controllers
- **`StudentApplicationController`** - Application management and workflow
- **`StudentController`** - Student CRUD and management operations
- **`AcademicHistoryController`** - Academic record management
- **`StudentTransferController`** - Transfer request handling
- **`StudentGraduationController`** - Graduation process management
- **`StudentDocumentController`** - Document upload and management

## 📡 API Endpoints

### Base URL
```
/api/students
```

### Student Applications
```
GET    /applications                    # List applications
POST   /applications                    # Submit application
GET    /applications/{id}              # View application
PUT    /applications/{id}              # Update application
DELETE /applications/{id}              # Delete application
POST   /applications/{id}/approve      # Approve application
POST   /applications/{id}/reject       # Reject application
GET    /applications/statistics/overview # Application statistics
```

### Students
```
GET    /students                       # List students
POST   /students                       # Create student
GET    /students/{id}                 # View student
PUT    /students/{id}                 # Update student
DELETE /students/{id}                 # Delete student
PATCH  /students/{id}/status          # Change student status
PATCH  /students/{id}/class           # Assign to class
GET    /students/{id}/academic-performance # Academic performance
GET    /students/statistics/overview  # Student statistics
```

### Academic History
```
GET    /academic-history               # List academic records
POST   /academic-history               # Create academic record
GET    /academic-history/{id}         # View academic record
PUT    /academic-history/{id}         # Update academic record
DELETE /academic-history/{id}         # Delete academic record
GET    /academic-history/student/{id} # Records by student
GET    /academic-history/subject/{id} # Records by subject
GET    /academic-history/class/{id}   # Records by class
GET    /academic-history/term/{id}    # Records by term
GET    /academic-history/academic-year/{id} # Records by academic year
GET    /academic-history/statistics/overview # Academic statistics
```

### Student Transfers
```
GET    /transfers                      # List transfers
POST   /transfers                      # Create transfer request
GET    /transfers/{id}                # View transfer
PUT    /transfers/{id}                # Update transfer
DELETE /transfers/{id}                # Delete transfer
POST   /transfers/{id}/approve        # Approve transfer
POST   /transfers/{id}/reject         # Reject transfer
GET    /transfers/student/{id}        # Transfers by student
GET    /transfers/campus/{id}         # Transfers by campus
GET    /transfers/statistics/overview # Transfer statistics
```

### Student Graduation
```
GET    /graduation                     # List graduations
POST   /graduation                     # Create graduation record
GET    /graduation/{id}               # View graduation
PUT    /graduation/{id}               # Update graduation
DELETE /graduation/{id}               # Delete graduation
POST   /graduation/{id}/issue         # Issue diploma
GET    /graduation/student/{id}       # Graduation by student
GET    /graduation/date-range         # Graduations by date range
GET    /graduation/statistics/overview # Graduation statistics
```

### Student Documents
```
GET    /documents                      # List documents
POST   /documents                      # Create document record
GET    /documents/{id}                # View document
PUT    /documents/{id}                # Update document
DELETE /documents/{id}                # Delete document
GET    /documents/student/{id}        # Documents by student
GET    /documents/type/{type}         # Documents by type
POST   /documents/upload              # Upload document
GET    /documents/download/{id}       # Download document
GET    /documents/statistics/overview # Document statistics
```

### Bulk Operations
```
POST   /bulk/students/import          # Bulk import students
POST   /bulk/students/export          # Bulk export students
POST   /bulk/academic-history/import  # Bulk import academic records
POST   /bulk/academic-history/export  # Bulk export academic records
```

### Reports & Analytics
```
GET    /reports/enrollment-trends     # Enrollment trend analysis
GET    /reports/academic-performance  # Academic performance reports
GET    /reports/transfer-analysis     # Transfer analysis reports
GET    /reports/graduation-analysis   # Graduation analysis reports
GET    /reports/student-demographics  # Student demographics reports
```

## 🛠️ Installation & Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Run Seeders
```bash
php artisan db:seed --class=StudentsManagementSeeder
```

### 3. Configuration
The service uses the `config/students.php` configuration file for:
- Rate limiting settings
- File upload configurations
- Student number generation rules
- Academic performance settings
- Notification preferences
- Cache and export settings

### 4. Environment Variables
```env
# Rate Limiting
STUDENTS_API_RATE_LIMIT=60
STUDENTS_API_RATE_LIMIT_HOUR=1000
STUDENTS_API_RATE_LIMIT_DAY=10000

# File Upload
STUDENTS_MAX_FILE_SIZE=10240
STUDENTS_STORAGE_PATH=students/documents
STUDENTS_STORAGE_DISK=public

# Student Numbers
STUDENT_NUMBER_PREFIX=STU
STUDENT_NUMBER_YEAR_FORMAT=Y
STUDENT_NUMBER_SEQUENCE_LENGTH=4

# Academic Performance
STUDENTS_PASSING_GRADE=D-
STUDENTS_HONOR_ROLL_GPA=3.5

# Notifications
STUDENTS_NOTIFICATIONS_ENABLED=true
STUDENTS_EMAIL_NOTIFICATIONS=true
STUDENTS_SMS_NOTIFICATIONS=false
STUDENTS_PUSH_NOTIFICATIONS=false
```

## 🔧 Usage Examples

### Creating a Student Application
```php
$application = StudentApplication::create([
    'school_id' => 1,
    'campus_id' => 1,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'dob' => '2008-05-15',
    'gender' => 'male',
    'email' => 'john.doe@example.com',
    'phone' => '+1234567890',
    'parent_name' => 'Robert Doe',
    'parent_email' => 'robert.doe@example.com',
    'parent_phone' => '+1234567891',
]);
```

### Approving an Application
```php
$application = StudentApplication::find(1);
$application->approve($reviewerId);

// This automatically creates a student record
```

### Managing Student Status
```php
$student = Student::find(1);
$student->activate();    // Set status to 'active'
$student->suspend();     // Set status to 'suspended'
$student->graduate();    // Set status to 'graduated'
$student->transfer();    // Set status to 'transferred'
```

### Academic Performance Analysis
```php
$student = Student::find(1);
$performance = $student->academicHistory()
    ->with(['subject', 'term', 'academicYear'])
    ->orderBy('academic_year_id', 'desc')
    ->get();

$overallGPA = $performance->avg('gpa');
```

### Document Management
```php
$document = StudentDocument::create([
    'student_id' => 1,
    'document_type' => 'Birth Certificate',
    'document_path' => 'students/documents/birth_cert_001.pdf',
    'original_filename' => 'birth_certificate.pdf',
    'mime_type' => 'application/pdf',
    'file_size' => 1024000,
]);
```

## 📊 Database Relationships

### Student Model Relationships
- **BelongsTo**: School, Campus, ClassRoom
- **HasMany**: Enrollments, Grades, AcademicHistory, Transfers, Graduation, Documents

### Academic History Relationships
- **BelongsTo**: Student, Subject, ClassRoom, Term, AcademicYear

### Student Transfer Relationships
- **BelongsTo**: Student, FromCampus, ToCampus, Reviewer

### Student Graduation Relationships
- **BelongsTo**: Student

### Student Document Relationships
- **BelongsTo**: Student

## 🔒 Security Features

- **Rate Limiting**: Configurable API rate limiting per endpoint
- **Input Validation**: Comprehensive validation rules for all inputs
- **File Upload Security**: MIME type and size validation
- **SQL Injection Protection**: Eloquent ORM with parameter binding
- **XSS Protection**: Output sanitization and encoding

## 📈 Performance Features

- **Database Indexing**: Optimized indexes for common queries
- **Eager Loading**: Relationship loading optimization
- **Caching**: Configurable cache for statistics and reports
- **Pagination**: Efficient data pagination for large datasets
- **Query Optimization**: Optimized database queries with scopes

## 🧪 Testing

### Running Tests
```bash
php artisan test --filter=Students
```

### Test Coverage
- Unit tests for all models
- Feature tests for all controllers
- Integration tests for API endpoints
- Database transaction tests

## 📝 API Documentation

The service is fully documented with Scribe and includes:
- Interactive API documentation
- Request/response examples
- Authentication requirements
- Rate limiting information
- Error code documentation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the API documentation
- Review the configuration options
- Consult the Laravel documentation

---

**Built with ❤️ using Laravel 12 and following OOP best practices** 
# Teachers Management System

This document provides a comprehensive overview of the Teachers Management System implemented in the Yousch API.

## Overview

The Teachers Management System is a comprehensive solution for managing teacher-related operations including profiles, leave management, document handling, performance evaluations, and timetable management.

## System Architecture

### Controllers
- **TeacherController** - Core teacher CRUD operations and profile management
- **TeacherLeaveController** - Leave request management and approval workflow
- **TeacherDocumentController** - Document upload, approval, and management
- **TeacherPerformanceController** - Performance evaluation and assessment
- **TeacherTimetableController** - Schedule and timetable management

### Repositories
- **TeacherRepository** - Teacher data operations
- **TeacherLeaveRepository** - Leave management operations
- **TeacherDocumentRepository** - Document management operations
- **TeacherPerformanceRepository** - Performance evaluation operations
- **TeacherTimetableRepository** - Timetable operations

### Models
- **Teacher** - Core teacher entity
- **TeacherLeave** - Leave request entity
- **TeacherDocument** - Document entity
- **TeacherPerformance** - Performance evaluation entity
- **TeacherTimetable** - Timetable entity

## API Endpoints

### Teachers Management
```
GET    /api/teachers                    - List all teachers
POST   /api/teachers                    - Create new teacher
GET    /api/teachers/{id}              - Get teacher details
PUT    /api/teachers/{id}              - Update teacher
DELETE /api/teachers/{id}              - Delete teacher
GET    /api/teachers/department/{id}   - Get teachers by department
GET    /api/teachers/faculty/{id}      - Get teachers by faculty
GET    /api/teachers/statistics        - Get teacher statistics
```

### Leave Management
```
GET    /api/teachers/leaves                    - List all leave requests
POST   /api/teachers/leaves                    - Submit leave request
GET    /api/teachers/leaves/{id}              - Get leave details
PUT    /api/teachers/leaves/{id}              - Update leave request
DELETE /api/teachers/leaves/{id}              - Cancel leave request
POST   /api/teachers/leaves/{id}/approve      - Approve leave request
POST   /api/teachers/leaves/{id}/reject       - Reject leave request
GET    /api/teachers/leaves/teacher/{id}      - Get teacher's leaves
GET    /api/teachers/leaves/type/{type}       - Get leaves by type
GET    /api/teachers/leaves/status/{status}   - Get leaves by status
GET    /api/teachers/leaves/pending           - Get pending leaves
GET    /api/teachers/leaves/statistics        - Get leave statistics
```

### Document Management
```
GET    /api/teachers/documents                    - List all documents
POST   /api/teachers/documents                    - Upload document
GET    /api/teachers/documents/{id}              - Get document details
PUT    /api/teachers/documents/{id}              - Update document
DELETE /api/teachers/documents/{id}              - Delete document
GET    /api/teachers/documents/{id}/download     - Download document
POST   /api/teachers/documents/{id}/approve      - Approve document
POST   /api/teachers/documents/{id}/reject       - Reject document
GET    /api/teachers/documents/teacher/{id}      - Get teacher's documents
GET    /api/teachers/documents/type/{type}       - Get documents by type
GET    /api/teachers/documents/status/{status}   - Get documents by status
GET    /api/teachers/documents/pending           - Get pending documents
GET    /api/teachers/documents/expired           - Get expired documents
GET    /api/teachers/documents/statistics        - Get document statistics
```

### Performance Management
```
GET    /api/teachers/performance                    - List all evaluations
POST   /api/teachers/performance                    - Create evaluation
GET    /api/teachers/performance/{id}              - Get evaluation details
PUT    /api/teachers/performance/{id}              - Update evaluation
DELETE /api/teachers/performance/{id}              - Delete evaluation
POST   /api/teachers/performance/{id}/publish      - Publish evaluation
POST   /api/teachers/performance/{id}/archive      - Archive evaluation
GET    /api/teachers/performance/teacher/{id}      - Get teacher's evaluations
GET    /api/teachers/performance/evaluator/{id}    - Get evaluator's evaluations
GET    /api/teachers/performance/period/{period}   - Get evaluations by period
POST   /api/teachers/performance/rating-range      - Get evaluations by rating range
GET    /api/teachers/performance/trends/{id}       - Get performance trends
GET    /api/teachers/performance/statistics        - Get performance statistics
```

### Timetable Management
```
GET    /api/teachers/timetables                    - List all timetables
POST   /api/teachers/timetables                    - Create timetable entry
GET    /api/teachers/timetables/{id}              - Get timetable details
PUT    /api/teachers/timetables/{id}              - Update timetable
DELETE /api/teachers/timetables/{id}              - Delete timetable
GET    /api/teachers/timetables/teacher/{id}      - Get teacher's timetable
GET    /api/teachers/timetables/class/{id}        - Get class timetable
GET    /api/teachers/timetables/subject/{id}      - Get subject timetable
GET    /api/teachers/timetables/day/{day}         - Get day timetable
GET    /api/teachers/timetables/academic-year/{id}/term/{id} - Get by period
GET    /api/teachers/timetables/teacher/{id}/weekly-schedule/{year}/{term} - Weekly schedule
GET    /api/teachers/timetables/class/{id}/weekly-schedule/{year}/{term} - Class schedule
POST   /api/teachers/timetables/check-conflicts   - Check time conflicts
GET    /api/teachers/timetables/statistics        - Get timetable statistics
```

## Features

### Teacher Profile Management
- Complete CRUD operations for teacher profiles
- Department and faculty assignments
- Employment type and status management
- Qualification and specialization tracking

### Leave Management
- Multiple leave types (sick, vacation, personal, etc.)
- Approval workflow with reviewer tracking
- Conflict detection for overlapping leave periods
- Emergency contact information
- Leave statistics and reporting

### Document Management
- File upload with validation
- Document type categorization
- Approval workflow
- Expiry date tracking
- File download functionality

### Performance Evaluation
- Multi-dimensional assessment (1-5 scale)
- Teaching effectiveness metrics
- Classroom management evaluation
- Professional development tracking
- Performance trends analysis

### Timetable Management
- Weekly schedule generation
- Time conflict detection
- Academic year and term support
- Room assignment
- Flexible querying by various criteria

## Business Rules

### Leave Management
- Teachers cannot have overlapping approved leaves
- Leave requests can only be modified when pending
- Rejection requires a reason
- Emergency contact information is mandatory

### Document Management
- File size limit: 10MB
- Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG
- Documents require approval workflow
- Expired documents are tracked separately

### Performance Evaluation
- Only one evaluation per teacher per period
- Evaluations start as drafts
- Published evaluations cannot be modified
- Rating scale: 1-5 with decimal support

### Timetable Management
- No time conflicts allowed for the same teacher
- Schedules are organized by academic year and term
- Weekly views available for teachers and classes
- Room assignments prevent double-booking

## Data Validation

All endpoints include comprehensive validation:
- Required field validation
- Data type validation
- Business rule validation
- Conflict detection
- Input sanitization

## Error Handling

- Consistent error response format
- HTTP status codes for different error types
- Detailed error messages
- Validation error details
- Exception handling with logging

## Rate Limiting

- API rate limiting: 60 requests per minute
- Teachers-specific rate limiting: Configurable per minute
- Rate limit responses include retry information

## Database Seeding

The system includes a comprehensive seeder (`TeachersManagementSeeder`) that creates:
- Sample faculties and departments
- Academic years and terms
- Sample teachers with complete profiles
- Leave requests, documents, and performance evaluations
- Timetable entries

## Usage Examples

### Creating a Teacher
```bash
POST /api/teachers
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@university.edu",
    "phone": "+1234567890",
    "date_of_birth": "1985-03-15",
    "gender": "male",
    "department_id": 1,
    "faculty_id": 1,
    "hire_date": "2024-01-15",
    "employment_type": "full-time",
    "qualification": "Ph.D. in Computer Science",
    "specialization": "Software Engineering"
}
```

### Submitting Leave Request
```bash
POST /api/teachers/leaves
{
    "teacher_id": 1,
    "leave_type": "vacation",
    "start_date": "2024-12-20",
    "end_date": "2024-12-25",
    "reason": "Holiday vacation",
    "emergency_contact": "Jane Doe",
    "emergency_phone": "+1987654321"
}
```

### Creating Performance Evaluation
```bash
POST /api/teachers/performance
{
    "teacher_id": 1,
    "evaluation_period": "Fall 2024",
    "evaluation_date": "2024-12-01",
    "evaluator_id": 1,
    "teaching_effectiveness": 5,
    "classroom_management": 4,
    "subject_knowledge": 5,
    "communication_skills": 4,
    "professional_development": 4,
    "student_engagement": 5,
    "assessment_quality": 4,
    "overall_rating": 4.5,
    "strengths": "Excellent subject knowledge",
    "areas_for_improvement": "More interactive activities",
    "recommendations": "Continue professional development"
}
```

## Testing

To test the system:

1. Run database migrations:
```bash
php artisan migrate
```

2. Seed the database:
```bash
php artisan db:seed --class=TeachersManagementSeeder
```

3. Test API endpoints using tools like Postman or curl

## Future Enhancements

- Email notifications for leave approvals/rejections
- Document versioning system
- Advanced reporting and analytics
- Integration with external HR systems
- Mobile application support
- Bulk import/export functionality

## Support

For technical support or questions about the Teachers Management System, please refer to the API documentation or contact the development team. 
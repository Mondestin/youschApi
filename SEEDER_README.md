# Academic Management System Seeders

This document explains how to use the seeders to populate your database with sample data for the Academic Management System.

## 🚀 Available Seeders

### 1. **AcademicManagementSeeder** (Comprehensive)
- **Purpose**: Creates a complete academic management system with realistic data
- **Data Created**: Schools, campuses, faculties, departments, courses, subjects, classes, timetables, exams, grades, enrollments, teacher assignments, announcements, and calendar events
- **Use Case**: Production-like environment, full system testing, demonstration

### 2. **DemoDataSeeder** (Simple)
- **Purpose**: Creates minimal data for quick testing
- **Data Created**: One school, three users (admin, teacher, student)
- **Use Case**: Quick testing, development, minimal setup

### 3. **DatabaseSeeder** (Main)
- **Purpose**: Orchestrates all seeders
- **Use Case**: Main seeding command

## 📋 Prerequisites

Before running the seeders, ensure you have:

1. **Database migrations run**: `php artisan migrate`
2. **Models properly imported**: All models should have correct namespace imports
3. **Database connection**: Proper database configuration in `.env`

## 🛠️ Usage Instructions

### Option 1: Run All Seeders (Recommended)
```bash
php artisan db:seed
```

This will run the `DatabaseSeeder` which includes the `AcademicManagementSeeder`.

### Option 2: Run Specific Seeder
```bash
# Run comprehensive academic management seeder
php artisan db:seed --class=AcademicManagementSeeder

# Run simple demo seeder
php artisan db:seed --class=DemoDataSeeder
```

### Option 3: Fresh Database + Seed
```bash
# Reset database and run all seeders
php artisan migrate:fresh --seed
```

## 📊 Data Created by AcademicManagementSeeder

### 👥 **Users**
- **Admin Users**: 2 (System Administrator, School Principal)
- **Teachers**: 5 (Dr. Sarah Johnson, Prof. Michael Chen, etc.)
- **Students**: 8 (Alex Smith, Maria Garcia, etc.)

### 🏫 **Academic Structure**
- **Schools**: 1 (Yousch International School)
- **Campuses**: 3 (Main, North, East)
- **Faculties**: 3 (Science & Technology, Business & Economics, Arts & Humanities)
- **Departments**: 4 (Computer Science, Mathematics, Business Administration, English Literature)
- **Courses**: 4 (BCS, BMATH, BBA, BAENG)
- **Subjects**: 5 (Programming, Data Structures, Calculus, Management, Literature)

### 🏫 **Classes & Scheduling**
- **Classes**: 8 (2 per course)
- **Timetables**: Weekly schedules for all classes
- **Labs**: Programming lab for CS subjects

### 📅 **Academic Calendar**
- **Academic Year**: 2024-2025
- **Terms**: 3 (Fall, Spring, Summer)
- **Calendar Events**: Holidays, breaks, exam periods

### 📝 **Assessment & Grading**
- **Grading Scheme**: Standard A-F scale
- **Grade Scales**: 5 grade levels with point values
- **Exams**: 15 total (3 per subject: midterm, final, quiz)

### 📚 **Student Data**
- **Enrollments**: 8 active student enrollments
- **Grades**: Sample grades for all enrolled students
- **Teacher Assignments**: Teachers assigned to classes

### 📢 **Communication**
- **Announcements**: 3 sample announcements
- **School Calendar**: 5 calendar events

## 🔐 Default Login Credentials

### **Admin Users**
- **System Administrator**: `admin@yousch.edu` / `password`
- **School Principal**: `principal@yousch.edu` / `password`

### **Teachers**
- **Dr. Sarah Johnson**: `sarah.johnson@yousch.edu` / `password`
- **Prof. Michael Chen**: `michael.chen@yousch.edu` / `password`
- **Dr. Emily Rodriguez**: `emily.rodriguez@yousch.edu` / `password`
- **Prof. David Thompson**: `david.thompson@yousch.edu` / `password`
- **Dr. Lisa Wang**: `lisa.wang@yousch.edu` / `password`

### **Students**
- **Alex Smith**: `alex.smith@student.yousch.edu` / `password`
- **Maria Garcia**: `maria.garcia@student.yousch.edu` / `password`
- **James Wilson**: `james.wilson@student.yousch.edu` / `password`
- **Emma Davis**: `emma.davis@student.yousch.edu` / `password`
- **Noah Brown**: `noah.brown@student.yousch.edu` / `password`
- **Sophia Lee**: `sophia.lee@student.yousch.edu` / `password`
- **Lucas Anderson**: `lucas.anderson@student.yousch.edu` / `password`
- **Olivia Taylor**: `olivia.taylor@student.yousch.edu` / `password`

## 🧪 Testing the System

After seeding, you can test the system by:

1. **API Endpoints**: Test all `/api/admin/academics/*` endpoints
2. **Authentication**: Use the provided credentials to test auth
3. **Data Relationships**: Verify that all relationships work correctly
4. **Business Logic**: Test enrollment, grading, and assignment features

## 🔄 Resetting Data

### Clear All Data
```bash
php artisan migrate:fresh
```

### Clear and Reseed
```bash
php artisan migrate:fresh --seed
```

### Clear Specific Tables
```bash
php artisan tinker
>>> DB::table('users')->truncate();
>>> DB::table('schools')->truncate();
>>> // ... other tables
```

## ⚠️ Important Notes

1. **Password**: All users have password `password` - change in production
2. **Data Volume**: The comprehensive seeder creates significant data - use demo seeder for quick testing
3. **Dependencies**: Seeders create data in the correct order to maintain referential integrity
4. **Production**: Never run seeders in production without reviewing the data first

## 🐛 Troubleshooting

### Common Issues

1. **"Class not found" errors**: Ensure all models are properly imported
2. **Database connection errors**: Check your `.env` database configuration
3. **Migration errors**: Run `php artisan migrate:status` to check migration status
4. **Memory issues**: For large datasets, increase PHP memory limit

### Debug Commands

```bash
# Check seeder status
php artisan db:seed --class=AcademicManagementSeeder --verbose

# Check database tables
php artisan tinker
>>> DB::table('users')->count();
>>> DB::table('schools')->count();
```

## 📈 Customization

To customize the seeded data:

1. **Edit the seeder files** in `database/seeders/`
2. **Modify data arrays** to match your requirements
3. **Add new seeders** for additional data types
4. **Update the DatabaseSeeder** to include new seeders

## 🎯 Next Steps

After seeding:

1. **Test API endpoints** with the provided credentials
2. **Verify data relationships** in your database
3. **Customize data** as needed for your use case
4. **Set up authentication** for your frontend application
5. **Configure Scribe documentation** to see all available endpoints

---

**Happy Seeding! 🌱** 
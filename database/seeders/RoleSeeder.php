<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creating roles...');

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => Role::ROLE_SUPER_ADMIN,
                'description' => 'Full system access with all permissions',
                'permissions' => ['*'], // All permissions
                'is_active' => true,
            ],
            [
                'name' => 'School Admin',
                'slug' => Role::ROLE_SCHOOL_ADMIN,
                'description' => 'Administrative access to school management',
                'permissions' => [
                    'school.manage',
                    'campus.manage',
                    'faculty.manage',
                    'department.manage',
                    'course.manage',
                    'subject.manage',
                    'class.manage',
                    'teacher.manage',
                    'student.manage',
                    'timetable.manage',
                    'exam.manage',
                    'attendance.manage',
                    'report.view',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Campus Admin',
                'slug' => Role::ROLE_CAMPUS_ADMIN,
                'description' => 'Administrative access to campus management',
                'permissions' => [
                    'campus.manage',
                    'faculty.manage',
                    'department.manage',
                    'course.manage',
                    'subject.manage',
                    'class.manage',
                    'teacher.manage',
                    'student.manage',
                    'timetable.manage',
                    'exam.manage',
                    'attendance.manage',
                    'report.view',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Teacher',
                'slug' => Role::ROLE_TEACHER,
                'description' => 'Teacher access to classes and students',
                'permissions' => [
                    'class.view',
                    'student.view',
                    'timetable.view',
                    'timetable.manage',
                    'exam.manage',
                    'attendance.manage',
                    'grade.manage',
                    'report.view',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Student',
                'slug' => Role::ROLE_STUDENT,
                'description' => 'Student access to their own data',
                'permissions' => [
                    'profile.view',
                    'profile.update',
                    'timetable.view',
                    'grade.view',
                    'attendance.view',
                    'exam.view',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Parent',
                'slug' => Role::ROLE_PARENT,
                'description' => 'Parent access to child\'s data',
                'permissions' => [
                    'student.view',
                    'timetable.view',
                    'grade.view',
                    'attendance.view',
                    'exam.view',
                    'report.view',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Accountant',
                'slug' => Role::ROLE_ACCOUNTANT,
                'description' => 'Financial management access',
                'permissions' => [
                    'fee.manage',
                    'payment.manage',
                    'financial.report',
                    'student.view',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Librarian',
                'slug' => Role::ROLE_LIBRARIAN,
                'description' => 'Library management access',
                'permissions' => [
                    'library.manage',
                    'book.manage',
                    'student.view',
                    'report.view',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('✅ ' . count($roles) . ' roles created successfully');
    }
}
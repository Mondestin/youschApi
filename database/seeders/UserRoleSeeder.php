<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Assigning roles to users...');

        // Get roles
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $schoolAdminRole = Role::where('slug', 'school_admin')->first();
        $teacherRole = Role::where('slug', 'teacher')->first();
        $studentRole = Role::where('slug', 'student')->first();

        if (!$superAdminRole || !$schoolAdminRole || !$teacherRole || !$studentRole) {
            $this->command->warn('⚠️  Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Get users by email (same approach as other seeders)
        $teacherEmails = [
            'sarah.johnson@yousch.edu',
            'michael.chen@yousch.edu',
            'emily.rodriguez@yousch.edu',
            'david.thompson@yousch.edu',
            'lisa.wang@yousch.edu'  // This one is created by AcademicManagementSeeder
        ];

        $adminEmails = [
            'admin@yousch.edu',
            'school.admin@yousch.edu'
        ];

        // Assign teacher roles
        $teachers = User::whereIn('email', $teacherEmails)->get();
        foreach ($teachers as $teacher) {
            if (!$teacher->hasRole('teacher')) {
                $teacher->roles()->attach($teacherRole->id, [
                    'assigned_by' => null,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                $this->command->info("✅ Assigned teacher role to: {$teacher->email}");
            }
        }

        // Assign admin roles
        $admins = User::whereIn('email', $adminEmails)->get();
        foreach ($admins as $admin) {
            if (!$admin->hasRole('school_admin')) {
                $admin->roles()->attach($schoolAdminRole->id, [
                    'assigned_by' => null,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                $this->command->info("✅ Assigned school_admin role to: {$admin->email}");
            }
        }

        // Assign super admin role to first user if no admin emails found
        if ($admins->isEmpty()) {
            $firstUser = User::first();
            if ($firstUser && !$firstUser->hasRole('super_admin')) {
                $firstUser->roles()->attach($superAdminRole->id, [
                    'assigned_by' => null,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                $this->command->info("✅ Assigned super_admin role to: {$firstUser->email}");
            }
        }

        // Assign student roles to remaining users
        $remainingUsers = User::whereNotIn('email', array_merge($teacherEmails, $adminEmails))->get();
        foreach ($remainingUsers as $user) {
            if (!$user->hasRole('student')) {
                $user->roles()->attach($studentRole->id, [
                    'assigned_by' => null,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                $this->command->info("✅ Assigned student role to: {$user->email}");
            }
        }

        $this->command->info('✅ User roles assigned successfully!');
    }
}
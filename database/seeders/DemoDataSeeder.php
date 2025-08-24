<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdminAcademics\School;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Demo Data Seeder...');

        // Create a simple school for demo
        $school = School::create([
            'name' => 'Demo School',
            'domain' => 'demo.edu',
            'contact_info' => 'Demo contact information',
            'address' => '123 Demo Street, Demo City',
            'phone' => '+1-555-DEMO',
            'email' => 'demo@demo.edu',
            'website' => 'https://demo.edu',
            'is_active' => true,
        ]);

        // Create demo admin user
        User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.edu',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Create demo teacher
        User::create([
            'name' => 'Demo Teacher',
            'email' => 'teacher@demo.edu',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Create demo student
        User::create([
            'name' => 'Demo Student',
            'email' => 'student@demo.edu',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Demo data created successfully!');
        $this->command->info('📧 Demo users created with email: password');
        $this->command->info('   - admin@demo.edu : password');
        $this->command->info('   - teacher@demo.edu : password');
        $this->command->info('   - student@demo.edu : password');
    }
} 
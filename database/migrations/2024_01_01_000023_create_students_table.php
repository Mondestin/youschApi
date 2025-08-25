<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained()->onDelete('set null');
            $table->string('student_number', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('dob');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_phone', 20)->nullable();
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'graduated', 'transferred', 'suspended', 'inactive'])->default('active');
            $table->string('profile_picture')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['school_id', 'campus_id']);
            $table->index(['class_id', 'status']);
            $table->index('student_number');
            $table->index('enrollment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
}; 
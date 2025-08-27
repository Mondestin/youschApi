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
        Schema::create('student_gpa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->decimal('gpa', 4, 2)->comment('term GPA');
            $table->decimal('cgpa', 4, 2)->nullable()->comment('cumulative GPA');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['term_id', 'academic_year_id']);
            $table->unique(['student_id', 'term_id', 'academic_year_id'], 'unique_student_term_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_gpa');
    }
}; 
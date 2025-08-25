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
        Schema::create('academic_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->decimal('marks', 5, 2)->nullable();
            $table->string('grade', 5)->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['subject_id', 'class_id']);
            $table->index(['term_id', 'academic_year_id']);
            $table->unique(['student_id', 'subject_id', 'class_id', 'term_id', 'academic_year_id'], 'unique_academic_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_history');
    }
}; 
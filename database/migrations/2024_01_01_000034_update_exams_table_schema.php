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
        Schema::table('exams', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['coordinator_id']);
            $table->dropForeign(['school_id']);
            
            // Add new columns
            $table->foreignId('lab_id')->nullable()->constrained()->onDelete('set null')->after('subject_id');
            $table->foreignId('exam_type_id')->constrained('exam_types')->onDelete('cascade')->after('lab_id');
            $table->foreignId('examiner_id')->nullable()->constrained('users')->onDelete('set null')->after('exam_type_id');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled')->after('examiner_id');
            
            // Drop old columns that are no longer needed
            $table->dropColumn(['type', 'coordinator_id', 'duration_minutes', 'total_marks', 'passing_marks', 'description', 'is_active', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Remove new columns
            $table->dropForeign(['lab_id']);
            $table->dropForeign(['exam_type_id']);
            $table->dropForeign(['examiner_id']);
            $table->dropColumn(['lab_id', 'exam_type_id', 'examiner_id', 'status']);
            
            // Add back old columns
            $table->string('name');
            $table->enum('type', ['internal', 'midterm', 'final', 'quiz', 'assignment', 'project']);
            $table->foreignId('coordinator_id')->constrained('users')->onDelete('cascade');
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
        });
    }
}; 
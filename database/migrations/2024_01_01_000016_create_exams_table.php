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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['internal', 'midterm', 'final', 'quiz', 'assignment', 'project']);
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('coordinator_id')->constrained('users')->onDelete('cascade');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
}; 
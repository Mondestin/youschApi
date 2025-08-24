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
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->nullable()->constrained()->onDelete('set null');
            $table->string('grade')->nullable(); // letter grade
            $table->decimal('score', 5, 2); // actual score obtained
            $table->decimal('max_score', 5, 2); // maximum possible score
            $table->decimal('percentage', 5, 2); // calculated percentage
            $table->text('remarks')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
}; 
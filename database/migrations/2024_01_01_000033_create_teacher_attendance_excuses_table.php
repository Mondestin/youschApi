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
        Schema::create('teacher_attendance_excuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('lab_id')->nullable()->constrained('labs')->onDelete('set null');
            $table->date('date');
            $table->string('reason', 255);
            $table->string('document_path', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_on')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['teacher_id', 'date']);
            $table->index(['class_id', 'date']);
            $table->index(['subject_id', 'date']);
            $table->index(['status', 'date']);
            $table->index(['reviewed_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_excuses');
    }
}; 
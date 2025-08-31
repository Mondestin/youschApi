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
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->decimal('gpa', 4, 2)->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->date('issued_date');
            $table->enum('format', ['PDF', 'Digital'])->default('Digital');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['class_id', 'term_id']);
            $table->index(['issued_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
}; 
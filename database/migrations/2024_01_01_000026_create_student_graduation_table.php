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
        Schema::create('student_graduation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('graduation_date');
            $table->string('diploma_number', 50)->unique();
            $table->enum('status', ['pending', 'issued'])->default('pending');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('student_id');
            $table->index('graduation_date');
            $table->index('diploma_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_graduation');
    }
}; 
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
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('min_score', 5, 2)->default(0.00);
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->decimal('passing_score', 5, 2)->default(60.00);
            $table->enum('grade_scale_type', ['letter', 'numeric', 'percentage'])->default('letter');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scheme_id')->constrained()->onDelete('cascade');
            $table->string('grade'); // A, B, C, D, F
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->decimal('grade_point', 3, 1); // 4.0, 3.0, 2.0, etc.
            $table->text('description')->nullable();
            $table->boolean('is_passing')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
        Schema::dropIfExists('grading_schemes');
    }
}; 
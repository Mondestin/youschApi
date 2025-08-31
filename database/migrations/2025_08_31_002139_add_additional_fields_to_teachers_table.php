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
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null')->after('address');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('set null')->after('department_id');
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'temporary'])->default('full-time')->after('hire_date');
            $table->string('qualification')->nullable()->after('employment_type');
            $table->text('specialization')->nullable()->after('qualification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropColumn(['department_id', 'faculty_id', 'employment_type', 'qualification', 'specialization']);
        });
    }
};

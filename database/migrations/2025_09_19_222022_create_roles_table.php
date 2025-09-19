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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'Super Admin', 'Teacher'
            $table->string('slug')->unique(); // e.g., 'super_admin', 'teacher'
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // Array of permissions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['slug', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

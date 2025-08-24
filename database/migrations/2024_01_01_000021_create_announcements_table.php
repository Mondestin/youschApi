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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('scope', ['school', 'campus', 'class', 'department', 'faculty'])->default('school');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_urgent')->default(false);
            $table->timestamp('publish_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('target_audience')->default('all');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
}; 
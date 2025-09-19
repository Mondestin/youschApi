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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Venue name (e.g., "Room 101", "Auditorium A")
            $table->text('description')->nullable(); // Optional description
            $table->integer('capacity')->nullable(); // Maximum capacity
            $table->string('location')->nullable(); // Physical location/building
            $table->enum('type', ['classroom', 'laboratory', 'auditorium', 'meeting_room', 'gymnasium', 'library', 'other'])->default('classroom'); // Venue type
            $table->boolean('is_active')->default(true); // Whether venue is available for booking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};

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
        Schema::table('timetables', function (Blueprint $table) {
            // Add venue_id column
            $table->foreignId('venue_id')->nullable()->after('end_time')->constrained('venues')->onDelete('set null');
            
            // Remove the old room column
            $table->dropColumn('room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // Add back the room column
            $table->string('room')->nullable()->after('end_time');
            
            // Remove venue_id column
            $table->dropForeign(['venue_id']);
            $table->dropColumn('venue_id');
        });
    }
};

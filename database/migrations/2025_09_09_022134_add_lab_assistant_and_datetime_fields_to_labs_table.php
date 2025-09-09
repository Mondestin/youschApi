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
        Schema::table('labs', function (Blueprint $table) {
            $table->foreignId('assistant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('start_datetime')->nullable();
            $table->datetime('end_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->dropForeign(['assistant_id']);
            $table->dropColumn(['assistant_id', 'start_datetime', 'end_datetime']);
        });
    }
};

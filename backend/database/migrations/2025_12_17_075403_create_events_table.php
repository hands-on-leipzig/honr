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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('first_program_id')->constrained()->onDelete('restrict');
            $table->foreignId('season_id')->constrained()->onDelete('restrict');
            $table->foreignId('level_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->date('date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('first_program_id');
            $table->index('season_id');
            $table->index('level_id');
            $table->index('location_id');
            $table->index('status');
            $table->index('date');
            $table->index('proposed_by_user_id');
            $table->unique(['first_program_id', 'season_id', 'level_id', 'location_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

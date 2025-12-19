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
        // Drop in order due to foreign key constraints
        Schema::dropIfExists('earned_badges');
        Schema::dropIfExists('badge_thresholds');
        Schema::dropIfExists('badges');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate badges table
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ['pending_icon', 'released'])->default('pending_icon');
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('restrict');
            $table->text('description')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('role_id');
            $table->index('sort_order');
        });

        // Recreate badge_thresholds table
        Schema::create('badge_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->integer('threshold_value');
            $table->string('level_name')->nullable();
            $table->string('icon_path')->nullable();
            $table->integer('sort_order');
            $table->timestamps();
            
            $table->index('badge_id');
            $table->index('sort_order');
            $table->unique(['badge_id', 'threshold_value']);
        });

        // Recreate earned_badges table
        Schema::create('earned_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->foreignId('current_threshold_id')->nullable()->constrained('badge_thresholds')->onDelete('set null');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('badge_id');
            $table->index('current_threshold_id');
            $table->unique(['user_id', 'badge_id']);
        });
    }
};

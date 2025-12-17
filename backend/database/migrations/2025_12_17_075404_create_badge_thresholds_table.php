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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_thresholds');
    }
};

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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['tick_box', 'grow']);
            $table->enum('status', ['pending_icon', 'released'])->default('pending_icon');
            $table->string('icon_path')->nullable();
            $table->foreignId('first_program_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('season_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('level_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('restrict');
            $table->text('description')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('first_program_id');
            $table->index('season_id');
            $table->index('level_id');
            $table->index('country_id');
            $table->index('role_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};

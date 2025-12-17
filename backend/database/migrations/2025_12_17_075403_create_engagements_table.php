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
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('role_id')->constrained()->onDelete('restrict');
            $table->foreignId('event_id')->constrained()->onDelete('restrict');
            $table->boolean('is_recognized')->default(false);
            $table->timestamp('recognized_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('role_id');
            $table->index('event_id');
            $table->index('is_recognized');
            $table->unique(['user_id', 'role_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};

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
        Schema::table('badges', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['first_program_id']);
            $table->dropForeign(['season_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['country_id']);
            
            // Drop indexes
            $table->dropIndex(['type']);
            $table->dropIndex(['first_program_id']);
            $table->dropIndex(['season_id']);
            $table->dropIndex(['level_id']);
            $table->dropIndex(['country_id']);
            
            // Drop columns
            $table->dropColumn([
                'type',
                'icon_path',
                'first_program_id',
                'season_id',
                'level_id',
                'country_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            // Re-add columns
            $table->enum('type', ['tick_box', 'grow'])->after('name');
            $table->string('icon_path')->nullable()->after('status');
            $table->foreignId('first_program_id')->nullable()->after('icon_path')->constrained()->onDelete('restrict');
            $table->foreignId('season_id')->nullable()->after('first_program_id')->constrained()->onDelete('restrict');
            $table->foreignId('level_id')->nullable()->after('season_id')->constrained()->onDelete('restrict');
            $table->foreignId('country_id')->nullable()->after('level_id')->constrained()->onDelete('restrict');
            
            // Re-add indexes
            $table->index('type');
            $table->index('first_program_id');
            $table->index('season_id');
            $table->index('level_id');
            $table->index('country_id');
        });
    }
};

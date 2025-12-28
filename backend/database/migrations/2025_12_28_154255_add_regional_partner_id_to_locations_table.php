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
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('regional_partner_id')
                ->nullable()
                ->after('country_id')
                ->constrained('regional_partners')
                ->onDelete('restrict');
            
            $table->index('regional_partner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['regional_partner_id']);
            $table->dropIndex(['regional_partner_id']);
            $table->dropColumn('regional_partner_id');
        });
    }
};

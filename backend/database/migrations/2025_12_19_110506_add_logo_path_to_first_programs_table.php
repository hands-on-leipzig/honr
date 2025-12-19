<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('first_programs', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('valid_to');
        });
    }

    public function down(): void
    {
        Schema::table('first_programs', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};

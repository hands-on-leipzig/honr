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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_notify_proposals')->default(false)->after('consent_to_newsletter');
            $table->boolean('email_tool_info')->default(false)->after('email_notify_proposals');
            $table->boolean('email_volunteer_newsletter')->default(false)->after('email_tool_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_notify_proposals', 'email_tool_info', 'email_volunteer_newsletter']);
        });
    }
};

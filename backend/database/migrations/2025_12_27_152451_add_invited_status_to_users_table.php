<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'invited' status to users table enum
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('requested', 'invited', 'active', 'disabled') NOT NULL DEFAULT 'requested'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'invited' status - convert any invited users to 'requested'
        DB::statement("UPDATE users SET status = 'requested' WHERE status = 'invited'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('requested', 'active', 'disabled') NOT NULL DEFAULT 'requested'");
    }
};

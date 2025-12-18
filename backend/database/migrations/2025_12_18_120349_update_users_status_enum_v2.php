<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change status enum from (requested, active, disabled) to (active, disabled)
     * - 'requested' users become 'active' (auto-confirmation via email link)
     * - 'disabled' stays as is
     */
    public function up(): void
    {
        // First update any 'requested' users to 'active'
        DB::statement("UPDATE users SET status = 'active' WHERE status = 'requested'");
        
        // Then modify the enum to only have 'active' and 'disabled'
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'disabled') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back 'requested' option
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('requested', 'active', 'disabled') NOT NULL DEFAULT 'requested'");
    }
};

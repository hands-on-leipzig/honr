<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum to include new values
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('requested', 'confirmed', 'active', 'disabled') NOT NULL DEFAULT 'requested'");
        
        // Migrate confirmed to active
        DB::statement("UPDATE users SET status = 'active' WHERE status = 'confirmed'");
        
        // Remove old value
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('requested', 'active', 'disabled') NOT NULL DEFAULT 'requested'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE users SET status = 'confirmed' WHERE status = 'active'");
        DB::statement("UPDATE users SET status = 'requested' WHERE status IN ('requested', 'disabled')");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('requested', 'confirmed') NOT NULL DEFAULT 'requested'");
    }
};

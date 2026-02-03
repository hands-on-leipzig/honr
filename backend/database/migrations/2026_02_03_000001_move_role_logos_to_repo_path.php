<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role logos moved from storage (gitignored) to public/images/logos/roles/ (in repo).
     */
    public function up(): void
    {
        DB::table('roles')
            ->whereNotNull('logo_path')
            ->where('logo_path', 'like', 'logos/roles/%')
            ->update([
                'logo_path' => DB::raw("REPLACE(logo_path, 'logos/roles/', 'images/logos/roles/')"),
            ]);
    }

    public function down(): void
    {
        DB::table('roles')
            ->whereNotNull('logo_path')
            ->where('logo_path', 'like', 'images/logos/roles/%')
            ->update([
                'logo_path' => DB::raw("REPLACE(logo_path, 'images/logos/roles/', 'logos/roles/')"),
            ]);
    }
};

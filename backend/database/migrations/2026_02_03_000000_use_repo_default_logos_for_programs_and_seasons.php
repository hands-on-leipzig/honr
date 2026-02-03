<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Program and season logos moved from storage (gitignored) to public/images/logos/ (in repo).
     * Point any null or legacy storage paths to the new default so the app is ready after deploy.
     */
    public function up(): void
    {
        $programDefault = 'images/logos/programs/default.svg';
        $seasonDefault = 'images/logos/seasons/default.svg';

        DB::table('first_programs')
            ->whereNull('logo_path')
            ->orWhere('logo_path', 'like', 'logos/first_programs/%')
            ->update(['logo_path' => $programDefault]);

        DB::table('seasons')
            ->whereNull('logo_path')
            ->orWhere('logo_path', 'like', 'logos/seasons/%')
            ->update(['logo_path' => $seasonDefault]);
    }

    public function down(): void
    {
        // Cannot reliably restore previous paths
    }
};

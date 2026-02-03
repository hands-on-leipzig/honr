<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize legacy logo_path values to current paths (images/logos/programs|seasons/...).
     * Ensures local and deployed envs (e.g. TST) use the same path format; no frontend workaround.
     * Idempotent: only updates rows that still have old prefixes.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // first_programs: logos/first_programs/X or storage/logos/first_programs/X → images/logos/programs/X
        if ($driver === 'mysql') {
            DB::table('first_programs')
                ->where(function ($q) {
                    $q->where('logo_path', 'like', 'logos/first_programs/%')
                        ->orWhere('logo_path', 'like', 'storage/logos/first_programs/%');
                })
                ->update([
                    'logo_path' => DB::raw("CONCAT('images/logos/programs/', SUBSTRING_INDEX(logo_path, '/', -1))"),
                ]);
            DB::table('seasons')
                ->where(function ($q) {
                    $q->where('logo_path', 'like', 'logos/seasons/%')
                        ->orWhere('logo_path', 'like', 'storage/logos/seasons/%');
                })
                ->update([
                    'logo_path' => DB::raw("CONCAT('images/logos/seasons/', SUBSTRING_INDEX(logo_path, '/', -1))"),
                ]);
        } else {
            $this->normalizeInPhp('first_programs', ['logos/first_programs/', 'storage/logos/first_programs/'], 'images/logos/programs/');
            $this->normalizeInPhp('seasons', ['logos/seasons/', 'storage/logos/seasons/'], 'images/logos/seasons/');
        }
    }

    private function normalizeInPhp(string $table, array $oldPrefixes, string $newPrefix): void
    {
        $rows = DB::table($table)->get(['id', 'logo_path']);
        foreach ($rows as $row) {
            if ($row->logo_path === null) {
                continue;
            }
            $path = ltrim($row->logo_path, '/');
            foreach ($oldPrefixes as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $filename = substr($path, strlen($prefix));
                    DB::table($table)->where('id', $row->id)->update(['logo_path' => $newPrefix . $filename]);
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        // Cannot reliably restore previous paths
    }
};

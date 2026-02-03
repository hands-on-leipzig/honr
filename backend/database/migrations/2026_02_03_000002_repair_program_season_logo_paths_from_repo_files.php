<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Repair program and season logo_path: migration 2026_02_03_000000 set all
     * to default.svg. Point each row to the actual file in the repo if it exists.
     */
    private const PROGRAM_DIR = 'images/logos/programs';
    private const SEASON_DIR = 'images/logos/seasons';
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

    public function up(): void
    {
        $programDir = public_path(self::PROGRAM_DIR);
        $seasonDir = public_path(self::SEASON_DIR);

        foreach (DB::table('first_programs')->get(['id']) as $row) {
            $path = $this->findExistingFile($programDir, self::PROGRAM_DIR, (string) $row->id);
            if ($path !== null) {
                DB::table('first_programs')->where('id', $row->id)->update(['logo_path' => $path]);
            }
        }

        foreach (DB::table('seasons')->get(['id']) as $row) {
            $path = $this->findExistingFile($seasonDir, self::SEASON_DIR, (string) $row->id);
            if ($path !== null) {
                DB::table('seasons')->where('id', $row->id)->update(['logo_path' => $path]);
            }
        }
    }

    private function findExistingFile(string $dirAbsolute, string $dirRelative, string $id): ?string
    {
        if (!File::isDirectory($dirAbsolute)) {
            return null;
        }
        foreach (self::EXTENSIONS as $ext) {
            $filename = $id . '.' . $ext;
            $fullPath = $dirAbsolute . DIRECTORY_SEPARATOR . $filename;
            if (File::exists($fullPath)) {
                return $dirRelative . '/' . $filename;
            }
        }
        return null;
    }

    public function down(): void
    {
        // No-op: cannot reliably revert to previous paths
    }
};

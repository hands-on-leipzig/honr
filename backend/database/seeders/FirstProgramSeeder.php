<?php

namespace Database\Seeders;

use App\Models\FirstProgram;
use Illuminate\Database\Seeder;

class FirstProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * FIRST LEGO League Program History:
     * - FIRST LEGO League: 1998-2013 (original, then split into Challenge/Explore)
     * - FIRST LEGO League Junior: 2004-2013 (renamed to Explore in 2013)
     * - FIRST LEGO League Explore: 2013-today (renamed from Junior)
     * - FIRST LEGO League Challenge: 2013-today (renamed from original FLL)
     * 
     * Note: Program switches happen in summer:
     * - valid_from is always August 1st
     * - valid_to is always July 31st (day before next program starts)
     */
    public function run(): void
    {
        // Original FIRST LEGO League (1998-2013, then split)
        FirstProgram::updateOrCreate(
            ['name' => 'FIRST LEGO League'],
            [
                'sort_order' => 1,
                'valid_from' => '1998-08-01',
                'valid_to' => '2013-07-31',
            ]
        );

        // FIRST LEGO League Junior (2004-2013, renamed to Explore)
        FirstProgram::updateOrCreate(
            ['name' => 'FIRST LEGO League Junior'],
            [
                'sort_order' => 2,
                'valid_from' => '2004-08-01',
                'valid_to' => '2013-07-31',
            ]
        );

        // FIRST LEGO League Challenge (2013-today, renamed from original FLL)
        FirstProgram::updateOrCreate(
            ['name' => 'FIRST LEGO League Challenge'],
            [
                'sort_order' => 3,
                'valid_from' => '2013-08-01',
                'valid_to' => null,
            ]
        );

        // FIRST LEGO League Explore (2013-today, renamed from Junior)
        FirstProgram::updateOrCreate(
            ['name' => 'FIRST LEGO League Explore'],
            [
                'sort_order' => 4,
                'valid_from' => '2013-08-01',
                'valid_to' => null,
            ]
        );

        // Default logo (in repo: public/images/logos/programs/default.svg) so app is ready after deploy
        FirstProgram::whereNull('logo_path')->update(['logo_path' => 'images/logos/programs/default.svg']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\FirstProgram;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seasons 1998-2013: Connected to "FIRST LEGO League" (original program)
     * Seasons 2014-2025: Connected to "FIRST LEGO League Challenge" (after split)
     */
    public function run(): void
    {
        $fll = FirstProgram::where('name', 'FIRST LEGO League')->first();
        $challenge = FirstProgram::where('name', 'FIRST LEGO League Challenge')->first();

        $seasons = [
            // 1998-2013: FIRST LEGO League
            ['start_year' => 1998, 'name' => 'FIRST LEGO League', 'program' => $fll],
            ['start_year' => 1999, 'name' => 'FIRST Contact', 'program' => $fll],
            ['start_year' => 2000, 'name' => 'Volcanic Panic', 'program' => $fll],
            ['start_year' => 2001, 'name' => 'Arctic Impact', 'program' => $fll],
            ['start_year' => 2002, 'name' => 'City Sights', 'program' => $fll],
            ['start_year' => 2003, 'name' => 'Mission Mars', 'program' => $fll],
            ['start_year' => 2004, 'name' => 'No Limits', 'program' => $fll],
            ['start_year' => 2005, 'name' => 'Ocean Odyssey', 'program' => $fll],
            ['start_year' => 2006, 'name' => 'Nano Quest', 'program' => $fll],
            ['start_year' => 2007, 'name' => 'Power Puzzle', 'program' => $fll],
            ['start_year' => 2008, 'name' => 'Climate Connections', 'program' => $fll],
            ['start_year' => 2009, 'name' => 'Smart Move', 'program' => $fll],
            ['start_year' => 2010, 'name' => 'Body Forward', 'program' => $fll],
            ['start_year' => 2011, 'name' => 'Food Factor', 'program' => $fll],
            ['start_year' => 2012, 'name' => 'Senior Solutions', 'program' => $fll],
            ['start_year' => 2013, 'name' => 'Nature\'s Fury', 'program' => $fll],
            
            // 2014-2025: FIRST LEGO League Challenge
            ['start_year' => 2014, 'name' => 'World Class', 'program' => $challenge],
            ['start_year' => 2015, 'name' => 'Trash Trek', 'program' => $challenge],
            ['start_year' => 2016, 'name' => 'Animal Allies', 'program' => $challenge],
            ['start_year' => 2017, 'name' => 'Hydro Dynamics', 'program' => $challenge],
            ['start_year' => 2018, 'name' => 'Into Orbit', 'program' => $challenge],
            ['start_year' => 2019, 'name' => 'City Shaper', 'program' => $challenge],
            ['start_year' => 2020, 'name' => 'RePLAY', 'program' => $challenge],
            ['start_year' => 2021, 'name' => 'Cargo Connect', 'program' => $challenge],
            ['start_year' => 2022, 'name' => 'Superpowered', 'program' => $challenge],
            ['start_year' => 2023, 'name' => 'Masterpiece', 'program' => $challenge],
            ['start_year' => 2024, 'name' => 'Submerged', 'program' => $challenge],
            ['start_year' => 2025, 'name' => 'Unearthed', 'program' => $challenge],
        ];

        foreach ($seasons as $season) {
            Season::updateOrCreate(
                [
                    'first_program_id' => $season['program']->id,
                    'start_year' => $season['start_year'],
                ],
                [
                    'name' => $season['name'],
                ]
            );
        }

        // Default logo (in repo: public/images/logos/seasons/default.svg) so app is ready after deploy
        Season::whereNull('logo_path')->update(['logo_path' => 'images/logos/seasons/default.svg']);
    }
}

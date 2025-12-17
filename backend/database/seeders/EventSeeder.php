<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\FirstProgram;
use App\Models\Season;
use App\Models\Level;
use App\Models\Location;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Note: Events are typically added via the admin UI since they require
     * multiple foreign keys (first_program_id, season_id, level_id, location_id)
     * and a date, making them complex to seed. However, once we have real events,
     * we can pull some representative examples into this seeder if needed for
     * testing or development purposes.
     */
    public function run(): void
    {
        // Events will be added via the admin UI
        // Example structure (if needed later):
        // $challenge = FirstProgram::where('name', 'FIRST LEGO League Challenge')->first();
        // $season = Season::where('name', 'Cargo Connect')->first();
        // $level = Level::where('name', 'Regionalwettbewerb')->first();
        // $location = Location::where('name', 'Universität Heidelberg, Mathematikon')->first();
        // 
        // Event::updateOrCreate(
        //     [
        //         'first_program_id' => $challenge->id,
        //         'season_id' => $season->id,
        //         'level_id' => $level->id,
        //         'location_id' => $location->id,
        //         'date' => '2023-11-15',
        //     ],
        //     ['status' => 'approved']
        // );
    }
}

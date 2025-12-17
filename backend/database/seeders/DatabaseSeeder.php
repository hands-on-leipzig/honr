<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run in order due to dependencies:
     * 1. FirstProgram (no dependencies)
     * 2. Season (needs FirstProgram)
     * 3. Level (no dependencies)
     * 4. Country (no dependencies)
     * 5. Role (needs FirstProgram)
     * 6. Location (needs Country)
     * 7. Event (needs FirstProgram, Season, Level, Location)
     */
    public function run(): void
    {
        $this->call([
            FirstProgramSeeder::class,
            SeasonSeeder::class,
            LevelSeeder::class,
            CountrySeeder::class,
            RoleSeeder::class,
            LocationSeeder::class,
            EventSeeder::class,
        ]);
    }
}

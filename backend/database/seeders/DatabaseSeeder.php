<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run in order due to dependencies:
     * 1. User (no dependencies)
     * 2. FirstProgram (no dependencies)
     * 3. Season (needs FirstProgram)
     * 4. Level (no dependencies)
     * 5. Country (no dependencies)
     * 6. Role (needs FirstProgram)
     * 7. Location (needs Country)
     * 8. Event (needs FirstProgram, Season, Level, Location)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
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

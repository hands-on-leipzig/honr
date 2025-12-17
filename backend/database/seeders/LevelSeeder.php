<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Level::updateOrCreate(
            ['name' => 'Regionalwettbewerb'],
            [
                'description' => null,
                'sort_order' => 1,
                'status' => 'approved',
            ]
        );

        Level::updateOrCreate(
            ['name' => 'Qualifier'],
            [
                'description' => null,
                'sort_order' => 2,
                'status' => 'approved',
            ]
        );

        Level::updateOrCreate(
            ['name' => 'Semi-Finale'],
            [
                'description' => null,
                'sort_order' => 3,
                'status' => 'approved',
            ]
        );

        Level::updateOrCreate(
            ['name' => 'Finale'],
            [
                'description' => null,
                'sort_order' => 4,
                'status' => 'approved',
            ]
        );

        Level::updateOrCreate(
            ['name' => 'Ausstellung'],
            [
                'description' => null,
                'sort_order' => 5,
                'status' => 'approved',
            ]
        );

        Level::updateOrCreate(
            ['name' => 'Open European Championship'],
            [
                'description' => null,
                'sort_order' => 6,
                'status' => 'approved',
            ]
        );

        Level::updateOrCreate(
            ['name' => 'World Finale'],
            [
                'description' => null,
                'sort_order' => 7,
                'status' => 'approved',
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::updateOrCreate(
            ['iso_code' => 'DE'],
            [
                'name' => 'Deutschland',
                'status' => 'approved',
            ]
        );

        Country::updateOrCreate(
            ['iso_code' => 'AT'],
            [
                'name' => 'Österreich',
                'status' => 'approved',
            ]
        );

        Country::updateOrCreate(
            ['iso_code' => 'CH'],
            [
                'name' => 'Schweiz',
                'status' => 'approved',
            ]
        );

        Country::updateOrCreate(
            ['iso_code' => 'HU'],
            [
                'name' => 'Ungarn',
                'status' => 'approved',
            ]
        );
    }
}

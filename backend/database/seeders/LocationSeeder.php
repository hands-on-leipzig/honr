<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Country;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $germany = Country::where('iso_code', 'DE')->first();
        $austria = Country::where('iso_code', 'AT')->first();
        $switzerland = Country::where('iso_code', 'CH')->first();
        $hungary = Country::where('iso_code', 'HU')->first();

        $locations = [
            [
                'name' => 'Universität Heidelberg, Mathematikon',
                'country' => $germany,
                'street_address' => 'Im Neuenheimer Feld 205',
                'city' => 'Heidelberg',
                'postal_code' => '69120',
                'latitude' => 49.4179,
                'longitude' => 8.6757,
            ],
            [
                'name' => 'Haus der Wissenschaft',
                'country' => $germany,
                'street_address' => 'Pockelsstraße 11',
                'city' => 'Braunschweig',
                'postal_code' => '38106',
                'latitude' => 52.2689,
                'longitude' => 10.5268,
            ],
            [
                'name' => 'Inda-Gymnasium Aachen',
                'country' => $germany,
                'street_address' => 'Gangolfsweg 52',
                'city' => 'Aachen',
                'postal_code' => '52076',
                'latitude' => 50.7496,
                'longitude' => 6.1561,
            ],
            [
                'name' => 'Donnersberghalle',
                'country' => $germany,
                'street_address' => 'Brühlgasse 10',
                'city' => 'Rockenhausen',
                'postal_code' => '67806',
                'latitude' => 49.6300,
                'longitude' => 7.8200,
            ],
            [
                'name' => 'BFI Tirol Bildungs GmbH',
                'country' => $austria,
                'street_address' => 'Ing.-Etzel-Straße 7',
                'city' => 'Innsbruck',
                'postal_code' => '6020',
                'latitude' => 47.2692,
                'longitude' => 11.4041,
            ],
            [
                'name' => 'Forum de l\'Arc',
                'country' => $switzerland,
                'street_address' => 'Rue Industrielle 98',
                'city' => 'Moutier',
                'postal_code' => '2740',
                'latitude' => 47.2800,
                'longitude' => 7.3700,
            ],
            [
                'name' => 'Neues Rathaus',
                'country' => $germany,
                'street_address' => 'Martin-Luther-Ring 4',
                'city' => 'Leipzig',
                'postal_code' => '04109',
                'latitude' => 51.3397,
                'longitude' => 12.3731,
            ],
            [
                'name' => 'Gemeinschaftsschule Harksheide',
                'country' => $germany,
                'street_address' => 'Am Exerzierplatz 20',
                'city' => 'Norderstedt',
                'postal_code' => '22844',
                'latitude' => 53.7000,
                'longitude' => 9.9833,
            ],
            [
                'name' => 'Hochschule Offenburg',
                'country' => $germany,
                'street_address' => 'Badstraße 24',
                'city' => 'Offenburg',
                'postal_code' => '77652',
                'latitude' => 48.4700,
                'longitude' => 7.9500,
            ],
            [
                'name' => 'Heinz-Nixdorf-MuseumsForum',
                'country' => $germany,
                'street_address' => 'Fürstenallee 7',
                'city' => 'Paderborn',
                'postal_code' => '33102',
                'latitude' => 51.7200,
                'longitude' => 8.7500,
            ],
            [
                'name' => 'SAP Gebäude ROT03',
                'country' => $germany,
                'street_address' => 'SAP-Allee 29',
                'city' => 'St. Leon-Rot',
                'postal_code' => '68789',
                'latitude' => 49.2667,
                'longitude' => 8.6167,
            ],
            [
                'name' => 'Festspielhaus Bregenz',
                'country' => $austria,
                'street_address' => 'Platz d. Wr. Symphoniker 1',
                'city' => 'Bregenz',
                'postal_code' => '6900',
                'latitude' => 47.5046,
                'longitude' => 9.7471,
            ],
            [
                'name' => 'Főnix Aréna',
                'country' => $hungary,
                'street_address' => 'Kassai út 28',
                'city' => 'Debrecen',
                'postal_code' => '4028',
                'latitude' => 47.5317,
                'longitude' => 21.6392,
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['name' => $location['name']],
                [
                    'country_id' => $location['country']->id,
                    'street_address' => $location['street_address'],
                    'city' => $location['city'],
                    'postal_code' => $location['postal_code'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'status' => 'approved',
                ]
            );
        }
    }
}

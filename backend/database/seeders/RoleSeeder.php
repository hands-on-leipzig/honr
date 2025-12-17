<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\FirstProgram;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $challenge = FirstProgram::where('name', 'FIRST LEGO League Challenge')->first();
        $explore = FirstProgram::where('name', 'FIRST LEGO League Explore')->first();

        // Challenge roles
        $challengeRoles = [
            ['name' => 'Moderator:in', 'sort_order' => 1, 'role_category' => 'volunteer'],
            ['name' => 'Coach:in Challenge', 'sort_order' => 2, 'role_category' => 'coach'],
            ['name' => 'Juror:in', 'sort_order' => 3, 'role_category' => 'volunteer'],
            ['name' => 'Juror:in Roboter-Design', 'sort_order' => 4, 'role_category' => 'volunteer'],
            ['name' => 'Juror:in Grundwerte', 'sort_order' => 5, 'role_category' => 'volunteer'],
            ['name' => 'Juror:in Teamwork', 'sort_order' => 6, 'role_category' => 'volunteer'],
            ['name' => 'Juror:in Forschung', 'sort_order' => 7, 'role_category' => 'volunteer'],
            ['name' => 'Schiedsrichter:in', 'sort_order' => 8, 'role_category' => 'volunteer'],
            ['name' => 'Robot-Checker:in', 'sort_order' => 9, 'role_category' => 'volunteer'],
            ['name' => 'Live Challenge-Juror', 'sort_order' => 10, 'role_category' => 'volunteer'],
            ['name' => 'Jury-Leiter:in', 'sort_order' => 11, 'role_category' => 'volunteer'],
            ['name' => 'Ober-Schiedsrichter:in', 'sort_order' => 12, 'role_category' => 'volunteer'],
            ['name' => 'Live-Challange-Leiter:in', 'sort_order' => 13, 'role_category' => 'volunteer'],
            ['name' => 'Regional-Partner:in Challenge', 'sort_order' => 14, 'role_category' => 'regional_partner'],
        ];

        // Explore roles
        $exploreRoles = [
            ['name' => 'Gutachter:in', 'sort_order' => 1, 'role_category' => 'volunteer'],
            ['name' => 'Coach:in Explore', 'sort_order' => 2, 'role_category' => 'coach'],
            ['name' => 'Regional-Partner:in Explore', 'sort_order' => 3, 'role_category' => 'regional_partner'],
        ];

        foreach ($challengeRoles as $role) {
            Role::updateOrCreate(
                [
                    'first_program_id' => $challenge->id,
                    'name' => $role['name'],
                ],
                [
                    'description' => null,
                    'sort_order' => $role['sort_order'],
                    'role_category' => $role['role_category'],
                    'status' => 'approved',
                ]
            );
        }

        foreach ($exploreRoles as $role) {
            Role::updateOrCreate(
                [
                    'first_program_id' => $explore->id,
                    'name' => $role['name'],
                ],
                [
                    'description' => null,
                    'sort_order' => $role['sort_order'],
                    'role_category' => $role['role_category'],
                    'status' => 'approved',
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tm@hands-on-technology.org'],
            [
                'password' => Hash::make('1234567:-)'),
                'nickname' => 'Thomas Madeya',
                'status' => 'confirmed',
                'is_admin' => true,
            ]
        );
    }
}

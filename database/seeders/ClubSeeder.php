<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Club;
use Illuminate\Support\Str;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = [
            ['name' => 'Aquatic Shark Swimming Club', 'short_name' => 'ASSC'],
            ['name' => 'Blue Dolphin Swim Team', 'short_name' => 'BDST'],
            ['name' => 'Coral Reef Swimming Academy', 'short_name' => 'CRSA'],
            ['name' => 'Deep Sea Mariners', 'short_name' => 'DSM'],
            ['name' => 'Elite Wave Swimmers', 'short_name' => 'EWS'],
            ['name' => 'Flying Fish Club', 'short_name' => 'FFC'],
            ['name' => 'Golden Gator Swimming', 'short_name' => 'GGS'],
            ['name' => 'Hydro Thunder Aquatic', 'short_name' => 'HTA'],
            ['name' => 'Island Breeze Swim Club', 'short_name' => 'IBSC'],
            ['name' => 'Jungle River Swimmers', 'short_name' => 'JRS'],
        ];

        foreach ($clubs as $club) {
            Club::updateOrCreate(
                ['name' => $club['name']],
                [
                    'uid' => (string) Str::uuid(),
                    'short_name' => $club['short_name'],
                    'coach_name' => 'Coach ' . fake()->firstName(),
                    'contact' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'website' => 'https://' . Str::slug($club['name']) . '.com',
                ]
            );
        }
    }
}

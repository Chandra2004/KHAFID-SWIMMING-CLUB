<?php

namespace Database\Seeders;

use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            RequirementParameterSeeder::class,
            // NotificationSeeder::class,
            ClubSeeder::class,
            FinanceAccountSeeder::class,
            CategorySeeder::class,
            EventSeeder::class,
            EventCategorySeeder::class,
            // RegistrationSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            nationality_info::class,
            permission_info::class,
            user_info::class,
            RolesOfUsersSeeder::class,
            national_role::class,
            referralTypeSeeder::class,
            HospitalSeeder::class,
            SourceSeeder::class,
            SourceTypeSeeder::class,
            DocumentTypeSeeder::class,
        ]);
    }
}
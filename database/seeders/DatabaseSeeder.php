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
            permission_info::class,       // ✅ 1. create permissions first
            RolesOfUsersSeeder::class,    // ✅ 2. create roles and assign permissions
            user_info::class,             // ✅ 3. create admin and assign all
            national_role::class,
            referralTypeSeeder::class,
            HospitalSeeder::class,
            SourceSeeder::class,
            SourceTypeSeeder::class,
            DocumentTypeSeeder::class,
        ]);
    }
}